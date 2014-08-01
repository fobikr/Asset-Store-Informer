<?php

require_once 'asset.php';

class AssetStore
{
    public static $curlTime = 0;
    
    public static $publisher_id;
    private static $session_id;
    
    private static $publisherResult = null;
    public static $assets;
    private static $reviews;

    public static function loadPublisherInfo() 
    {
        AssetStore::$session_id = strdecode(uas_session, $GLOBALS['salt']);
        
        $res = DB::tableg('publisher')->first();
        if (!empty($res))
        {
            self::$publisher_id = $res['id'];
            Informer::$lastSendTime = strtotime($res['lastSendTime']);
            return TRUE;
        }
        else return FALSE;
    }
    
    public static function checkPublisherRating($response)
    {
        if ($response === FALSE) return;
        
        $obj = json_decode($response);
        
        if (function_exists("json_last_error") && json_last_error() !== JSON_ERROR_NONE) return;
        
        $overview = $obj->{'overview'};
        
        if (!empty($overview->{'rating'}))
        {
            $rating = $overview->{'rating'};
            if (!empty($rating->{'count'})) $rating_count = $rating->{'count'};
            else $rating_count = 0;
            
            if (!empty($rating->{'average'})) $rating_average = $rating->{'average'};
            else $rating_average = 0;
        }
        else 
        {
            $rating_count = 0;
            $rating_average = 0;
        }

        $res = DB::tableg('publisher')->first();
        if (empty($res)) return;
        
        $data = array();
        if ($overview->{'name'} != $res['name']) $data['name'] = $overview->{'name'};
        if ($overview->{'description'} != $res['description']) $data['description'] = $overview->{'description'};
        if ($overview->{'url'} != $res['url']) $data['url'] = $overview->{'url'};
        if ($overview->{'support_email'} != $res['support_email']) $data['support_email'] = $overview->{'support_email'};
        if ($overview->{'support_url'} != $res['support_url']) $data['support_url'] = $overview->{'support_url'};
        if (!empty($overview->{'keyimage'})) 
        {
            $keyimage = $overview->{'keyimage'};
            if ($keyimage->{'small'} != $res['keyimage_small']) $data['keyimage_small'] = $keyimage->{'small'};
            if ($keyimage->{'big'} != $res['keyimage_big']) $data['keyimage_big'] = $keyimage->{'big'};
        }
        
        if ($rating_average != $res['rating_average']) $data['rating_average'] = $rating_average;

        if ($res['rating_count'] == $rating_count) 
        {
            if (!empty($data)) DB::tableg('publisher')->update($data);
            return;
        }
        
        $data['rating_count'] = $rating_count;
        
        DB::tableg('publisher')->update($data);
        $ratingOffset = $rating_count - $res['rating_count'];
        
        $findedOffset = AssetStore::checkAssetRatings();
        if ($findedOffset < $ratingOffset) 
        {
            $rating_data = array(
                'offset' => $ratingOffset - $findedOffset,
                'asset_id' => 0,
                'time' => gmdate("Y-m-d H:i:s", time()),
            );
            DB::tableg('ratings')->insert($rating_data);

            print "Unknown has new ratings: " . ($ratingOffset - $findedOffset) . "<br/>";
        }
    }
    
    public static function checkAssetRatings($allowUpdateReviews = TRUE) 
    {
        $offset = 0;
        
        self::loadPublisherResult();
        if (!self::$publisherResult) return $offset;
        
        $results = self::$publisherResult;
        self::loadAssets();
        
        foreach ($results as $asset_res) 
        {
            $title = $asset_res->{'title'};
            $asset = self::getAssetByTitle($title);
            if (!$asset) continue;
            
            if ($asset->id == 0) $asset->updateInfoFromResult($asset_res);

            $rating = $asset_res->{'rating'};
            if (!empty($asset_res->{'rating'}))
            {
                if (!empty($rating->{'count'})) $rating_count = $rating->{'count'};
                else $rating_count = 0;
                
                if (!empty($rating->{'average'})) $rating_average = $rating->{'average'};
                else $rating_average = 0;
            }
            else
            {
                $rating_count = 0;
                $rating_average = 0;
            }
            
            if ($rating_count <= $asset->rating_count) continue;
            
            $token = $GLOBALS['settings']['token'];
            $reviews = CURL($GLOBALS['settings']['assetstore_server'] . "api/content/comments/" . $asset->id . ".json", array(
                "header" => array('X-Unity-Session: ' . $token . $token . $token),
                "customrequest" => "GET"
            ));
            
            $data = array(
                'rating_count' => $rating_count, 
                'rating_average' => $rating_average
            );
            DB::tableg('assets')->where('id', $asset->id)->update($data);
            
            $rating_offset = $rating_count - $asset->rating_count;
            $offset += $rating_offset;
            
            if ($allowUpdateReviews && !self::UpdateReviews($reviews, FALSE))
            {
                $rating_data = array(
                    'count' => $rating_count,
                    'offset' => $rating_offset,
                    'oldrating' => $asset->rating_average,
                    'newrating' => $rating_average,
                    'asset_id' => $asset->id,
                    'time' => gmdate("Y-m-d H:i:s", time()),
                );
                DB::tableg('ratings')->insert($rating_data);

                Informer::Add('rating', time(), $asset->title, array(
                    "oldrating" => $asset->rating_average,
                    "newrating" => $rating_average
                ));
            }
        }
        return $offset;
    }
    
    /**
     * 
     * @param type $assets
     * @param type $title
     * @return Asset
     */
    public static function getAssetByTitle($title) 
    {
        foreach (self::$assets as $asset)
        {
            if ($asset->title === $title)
                return $asset;
        }
    }

    public static function getAssetFromSales($monthID = NULL)
    {
        if ($monthID === NULL) $monthID = gmdate("Ym");
        $url = $GLOBALS['settings']['assetstore_publisher_server'] . "api/publisher-info/sales/" . self::$publisher_id . "/$monthID.json";
        
        $response = CURL($url, array(
            "header" => array('X-Unity-Session: ' . strdecode(uas_session, $GLOBALS['salt'])),
            "customrequest" => "GET"
        ));
        
        if ($response === FALSE)
        {
            self::getXUnitySession();
            $response = CURL($url, array(
                "header" => array('X-Unity-Session: ' . strdecode(uas_session, $GLOBALS['salt'])),
                "customrequest" => "GET"
            ));
        }
        
        if ($response === FALSE) return FALSE;
        $obj = json_decode($response);
        $aaData = $obj->{'aaData'};
        $objRes = $obj->{'result'};

        if ($aaData !== NULL) 
        {
            $assets = array();
            for ($i = 0; $i < count($aaData); $i++) 
            {
                $asset = new Asset();
                $asset->loadFromAAData($aaData[$i], $objRes[$i]->{'short_url'});
                $assets[] = $asset;
            }
            return $assets;
        }
    }
    
    public static function getAssetFromResult($title)
    {
        self::loadPublisherResult();
        if(self::$publisherResult)
        {
            foreach (self::$publisherResult as $res)
            {
                if ($res->{'title'} == $title) return $res;
            }
        }
    }
    
    public static function getXUnitySession()
    {
        $publisher_js = CURL($GLOBALS['settings']['assetstore_publisher_server'] . "resources/javascripts/publisher.js");
        if ($publisher_js === FALSE) return FALSE;
        
        preg_match("/var token = '(\w*)'/i", $publisher_js, $matches);
        $token = $matches[1];
        
        $xsession = CURL($GLOBALS['settings']['assetstore_publisher_server'] . "login", array(
            "header" => array('X-Unity-Session: ' . $token . $token . $token),
            "postfields" => "user=" . strdecode(uas_login, $GLOBALS['salt']) . "&pass=" . strdecode(uas_password, $GLOBALS['salt']) . "&skip_terms=true"
        ));
        
        if ($xsession === FALSE) return FALSE;
        
        self::$session_id = $xsession . $token . $token;
        if (($settings = file_get_contents("php/settings.php")) === FALSE) return FALSE;
        
        $settings = preg_replace("/uas_session = \"(.*)\"/", 'uas_session = "' . strencode(self::$session_id, $GLOBALS['salt']) . '"', $settings);
        file_put_contents("php/settings.php", $settings);
        
        return TRUE;
    }

    public static function loadAssets()
    {
        if (!empty(self::$assets)) return;
        
        $asset_rows = DB::tableg('assets')->get();
        if (empty($asset_rows)) 
        {
            Log::f("Cannot load assets", "error.log");
            exit();
        }
        
        self::$assets = array();
        
        while ($row = $asset_rows->fetchArray())
        {
            $asset = new Asset();
            $asset->loadLocal($row);
            self::$assets[] = $asset;
        }
    }
    
    public static function loadPublisherResult()
    {
        if (self::$publisherResult) return;
        $url = $GLOBALS['settings']['assetstore_server'] . "api/publisher/results/" . self::$publisher_id . ".json";
        $response = CURL($url, array(
            "header" => array('X-Unity-Session: ' . strdecode(uas_session, $GLOBALS['salt'])),
            "customrequest" => "GET"
        ));
        if ($response)
        {
            $obj = json_decode($response);
            self::$publisherResult = $obj->{'results'};
        }
    }
    
    public static function updateAssetInfo($response)
    {
        if ($response === FALSE) return;
        
        $obj = json_decode($response);
        $content = $obj->{'content'};
        $category = $content->{'category'};
        $price = $content->{'price'};
        $version_id = $content->{'version_id'};
        $asset_id = $content->{'id'};
        $pubdate = gmdate("Y-m-d H:i:s", strtotime($content->{'pubdate'}));
        
        foreach (self::$assets as $asset)
        {
            if ($asset->id == $asset_id)
            {
                $curAsset = $asset;
                break;
            }
        }
        
        if (!isset($curAsset)) return;
        
        $data = array();
        
        if ($version_id != $curAsset->version_id) 
        {
            $version = $content->{'version'};
            
            $data["version"] = $content->{'version'};
            $data["version_id"] = $version_id;

            DB::tableg('events')->insert(array(
                "type" => "version_changed",
                "asset_id" => $asset_id,
                "time" => gmdate("Y-m-d H:i:s", time()),
                "info" => $version
            ));

            Informer::Add("event", time(), $content->{'title'}, array(
                "message" => "The new version ($version) has been accepted."
            ));
        }
        
        if ($curAsset->min_unity_version != $content->{'min_unity_version'}) $data['min_unity_version'] = $content->{'min_unity_version'};
        if ($curAsset->size != $content->{'size'}) $data['size'] = $content->{'size'};
        if ($curAsset->category_label != $category->{'label'}) $data['category_label'] = $category->{'label'};
        if ($curAsset->category_short != $category->{'short_url'}) $data['category_short_url'] = $category->{'short_url'};
        if ($curAsset->description != $content->{'description'}) $data['description'] = $content->{'description'};
        if ($curAsset->title != $content->{'title'}) $data['title'] = $content->{'title'};
        if ($curAsset->publishnotes != $content->{'publishnotes'}) $data['publishnotes'] = $content->{'publishnotes'};
        if ($curAsset->short_url != $content->{'short_url'}) $data['short_url'] = $content->{'short_url'};
        if ($curAsset->pubdate != $pubdate) $data['pubdate'] = $pubdate;
        if ($curAsset->price != intval($price->{'USD'})) $data['price'] = intval($price->{'USD'});
        
        if (!empty($content->{'keyimage'}))
        {
            $keyimage = $content->{'keyimage'};
            if (!empty($keyimage->{'small'}) && $curAsset->keyimage_small != $keyimage->{'small'}) $data["keyimage_small"] = $keyimage->{'small'};
            if (!empty($keyimage->{'icon'}) && $curAsset->keyimage_icon != $keyimage->{'icon'}) $data["keyimage_icon"] = $keyimage->{'icon'};
            if (!empty($keyimage->{'big'}) && $curAsset->keyimage_big != $keyimage->{'big'}) $data["keyimage_big"] = $keyimage->{'big'};
        }
        
        if (!empty($data)) DB::tableg('assets')->where('id', $asset_id)->update($data);
    }
    
    public static function updateReviews($response, $allowCheckRating = TRUE)
    {
        if ($response === FALSE) return FALSE;
        
        $result = FALSE;
        
        $obj = json_decode($response);
        $comments = $obj->{'comments'};

        if ($comments)
        {
            if (empty(self::$reviews)) 
            {
                self::$reviews = DB::tableg('reviews')->get();
                if (empty(self::$reviews)) return FALSE;
                self::$reviews = self::$reviews->fetchAll();
            }
            
            foreach ($comments as $comment)
            {
                if (self::UpdateReview($comment)) $result = TRUE;
            }
        }
        
        if ($result && $allowCheckRating)
        {
            $token = $GLOBALS['settings']['token'];
            $rating_response = CURL($GLOBALS['settings']['assetstore_server'] . "api/publisher/overview/" . self::$publisher_id . ".json", array(
                "header" => array('X-Unity-Session: ' . $token . $token . $token),
                "customrequest" => "GET"
            ));
            
            if (!$rating_response) return $result;
            
            $robj = json_decode($rating_response);
            $overview = $robj->{'overview'};
        
            if (!empty($overview->{'rating'}))
            {
                $rating = $overview->{'rating'};
                if (!empty($rating->{'count'})) $rating_count = $rating->{'count'};
                else $rating_count = 0;

                if (!empty($rating->{'average'})) $rating_average = $rating->{'average'};
                else $rating_average = 0;
            }
            else
            {
                $rating_count = 0;
                $rating_average = 0;
            }
            
            DB::tableg('publisher')->update(array(
                "rating_count" => $rating_count,
                "rating_average" => $rating_average
            ));
            
            self::checkAssetRatings(FALSE);
        }
        
        return $result;
    }
    
    private static function UpdateReview($comment)
    {
        $user = $comment->{'user'};
        $helpful = $comment->{'is_helpful'};
        $asset_id = $comment->{'link'}->{'id'};
        
        $data = array(
            'date' => gmdate("Y-m-d H:i:s", strtotime($comment->{'date'})),
            'version' => $comment->{'version'},
            'subject' => $comment->{'subject'},
            'full' => $comment->{'full'},
            'rating' => $comment->{'rating'},
            'user_name' => $user->{'name'},
            'user_id' => $user->{'id'},
            'id' => $comment->{'id'},
            'asset_id' => $asset_id,
            'helpful_count' => $helpful->{'count'},
            'helpful_score' => $helpful->{'score'}
        );
        
        if (!empty($comment->{'replies'}))
        {
            $replies = $comment->{'replies'};
            $reply = $replies[0];
            $data['reply_subject'] = $reply->{'subject'};
            $data['reply_full'] = $reply->{'full'};
            $data['reply_date'] = gmdate("Y-m-d H:i:s", strtotime($reply->{'date'}));
        }

        $review = NULL;
        
        foreach (self::$reviews as $r)
        {
            if ($r['id'] == $data['id'])
            {
                $review = $r;
                break;
            }
        }

        if ($review)
        {
            $changed = FALSE;
            if ($review['subject'] != $data['subject']) $changed = TRUE;
            elseif ($review['full'] != $data['full']) $changed = TRUE;
            elseif ($review['rating'] != $data['rating']) $changed = TRUE;
            elseif ($review['helpful_count'] != $data['helpful_count']) $changed = TRUE;
            elseif ($review['helpful_score'] != $data['helpful_score']) $changed = TRUE;
            elseif (!empty ($data['reply_subject']))
            {
                if ($review['reply_subject'] != $data['reply_subject']) $changed = TRUE;
                elseif ($review['reply_full'] != $data['reply_full']) $changed = TRUE;
            }

            if ($changed) DB::tableg('reviews')->where('id', $data['id'])->update($data);
        }
        else 
        {
            DB::tableg('reviews')->insert($data);
            
            $asset = DB::tableg('assets')->where('id', $asset_id)->first();
            
            Informer::Add("review", time(), $asset['title'], array(
                'subject' => $data['subject'],
                'full' => $data['full'],
                'rating' => $data['rating'],
                'user_id' => $data['user_id'],
                'user_name' => $data['user_name']
            ));
            
            return TRUE;
        }
        return FALSE;
    }
}
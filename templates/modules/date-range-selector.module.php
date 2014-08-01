<?php print LoadCSS('date-range-selector', 'module'); ?>
<div class="date-range-selector">
    <?php print LoadJS("jquery.mtz.monthpicker", 'base'); ?>
    <script>
        ASI.firstYear = <?php print $GLOBALS['first_year']; ?>;
    </script>
    
    <div class="date-range-selector-options<?php if ($GLOBALS['category'] != 'publisher') print " hidden"; ?>">
        <section id="date-range-selector-options-type">
            <ul data-option-key="filter" class="option-set">
                <li><a class="toggle-button selected" data-option-value="sales">Sales</a></li>
                <li><a class="toggle-button" data-option-value="summary-count">Summary count</a></li>
                <li><a class="toggle-button" data-option-value="summary-gross">Summary gross</a></li>
            </ul>
        </section>
        <section id="date-range-selector-options-section" style="float:right">
            <ul data-option-key="filter" class="option-set">
                <li><a class="toggle-button" data-option-value="last7days">Last 7 days</a></li>
                <li><a class="toggle-button" data-option-value="week">Week</a></li>
                <li><a class="toggle-button" data-option-value="month">Month</a></li>
                <li><a class="toggle-button" data-option-value="year">Year</a></li>
                <li><a class="toggle-button" data-option-value="custom">Custom</a></li>
            </ul>
        </section>
    </div>
    <div class="date-range-selector-filters" align="right">
        <div class="date-range-selector-filters-wrapper" align="left">
            <div class="date-range-selector-week hidden">
                <p>Week: <input type="text" class="weekpicker"></p>
            </div>
            <div class="date-range-selector-month hidden">
                <p>Month: <input type="text" class="monthpicker"></p>
            </div>
            <div class="date-range-selector-year hidden">
                <div id="date-range-selector-year-combobox">
                    <span><?php print date("Y"); ?></span>
                    <ul class="dropdown">
                        <?php 
                            $year = date("Y");
                            $count = 0;
                            while ($year >= $GLOBALS['first_year'] && $count < 20):
                        ?>
                            <li><a><?php print $year; ?></a></li>
                        <?php 
                                $year--;
                                $count++;
                            endwhile; 
                        ?>
                    </ul>
                </div>
            </div>
            <div class="date-range-selector-custom hidden">
                <div>
                    <label for="customdateselectorfrom">From</label>
                    <input type="text" name="customdateselectorfrom" id="date-range-selector-custom-from" class="datepicker">
                </div>
                <div>
                    <label for="customdateselectorto">to</label>
                    <input type="text" name="customdateselectorto" id="date-range-selector-custom-to" class="datepicker">
                </div>
                <div id="date-range-selector-custom-show" class="button">Show</div>
            </div>
        </div>
    </div>
    
    <?php print LoadJS('data-range-selector', 'module'); ?>
</div>

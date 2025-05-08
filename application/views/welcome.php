<!-- START WIDGETS -->
<div class="row">
    <div class="col-md-3">

        <!-- START WIDGET SLIDER -->
        <div class="widget widget-default widget-carousel">
            <div class="owl-carousel" id="owl-example">
                <?php foreach ($header_daily_report as $key => $value): ?>
                    <div>
                        <div class="widget-title"><?= str_replace('_', ' ', $key) ?></div>
                        <div class="widget-subtitle">Hari ini</div>
                        <div class="widget-int"><?= $value ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- END WIDGET SLIDER -->

    </div>
    <div class="col-md-3">

        <!-- START WIDGET MESSAGES -->
        <div class="widget widget-default widget-item-icon">
            <div class="widget-item-left">
                <span class="fa fa-envelope"></span>
            </div>
            <div class="widget-data">
                <div class="widget-int num-count">48</div>
                <div class="widget-title">New messages</div>
                <div class="widget-subtitle">In your mailbox</div>
            </div>
        </div>
        <!-- END WIDGET MESSAGES -->

    </div>
    <div class="col-md-3">

        <!-- START WIDGET REGISTRED -->
        <div class="widget widget-default widget-item-icon">
            <div class="widget-item-left">
                <span class="fa fa-user"></span>
            </div>
            <div class="widget-data">
                <div class="widget-int num-count">375</div>
                <div class="widget-title">Registred users</div>
                <div class="widget-subtitle">On your website</div>
            </div>
        </div>
        <!-- END WIDGET REGISTRED -->

    </div>
    <div class="col-md-3">

        <!-- START WIDGET CLOCK -->
        <div class="widget widget-info widget-padding-sm">
            <div class="widget-big-int plugin-clock">00:00</div>
            <div class="widget-subtitle plugin-date">Loading...</div>
        </div>
        <!-- END WIDGET CLOCK -->

    </div>
</div>
<!-- END WIDGETS -->
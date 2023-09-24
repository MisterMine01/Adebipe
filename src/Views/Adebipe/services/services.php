<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            <?php
                require __DIR__ . '/services.css';
            ?>
        </style>
    </head>
    <body>
        <div class="summary">
            <div class="summary-item">
                <p class="summary-item-name">Services</p>
                <div class="summary-item-list">
                    <? foreach ($services as $service): ?>
                        <a href="#<?php echo $service['long_name'] ?>"><?php echo $service['name'] ?></a>
                    <? endforeach ?>
                </div>
            </div>
        </div>
        <div class="services-list">
            <? foreach ($services as $service): ?>
                <details class="service-item" id="<?php echo $service['long_name'] ?>">
                    <summary class="service-name"><?php echo $service['name'] ?></summary>
                    <div class="method-list">
                        <? foreach ($service['methods'] as $method): ?>
                            <div class="method-item">
                                <p class="method-name"><?php echo $method['name'] ?></h2>
                                <div class="method-parameters-list">
                                    <? foreach ($method['parameters'] as $parameter): ?>
                                        <div class="method-parameter-item">
                                            <p class="method-parameter-name"><?php echo $parameter['name'] ?></p>
                                            <p class="method-parameter-type">
                                                <a href="#<?php echo $parameter['type'] ?>"><?php echo $parameter['type'] ?></a>
                                            </p>
                                            <p class="method-parameter-comment"><?php echo $parameter['comment'] ?? "" ?></p>
                                        </div>
                                    <? endforeach ?>
                                </div>
                                <p class="method-comment"><?php echo $method['comment'] ?></p>
                            </div>
                        <? endforeach ?>
                    </div>
                </details>
            <? endforeach ?>
        </div>
    </body>
</html>
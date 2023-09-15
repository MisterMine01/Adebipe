<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            <?php
                include __DIR__ . '/services.css';
            ?>
        </style>
    </head>
    <body>
        <div class="summary">
            <div class="summary-item">
                <p class="summary-item-name">Services</p>
                <div class="summary-item-list">
                    <? foreach ($services as $service): ?>
                        <a href="#<?= $service['long_name'] ?>"><?= $service['name'] ?></a>
                    <? endforeach ?>
                </div>
            </div>
        </div>
        <div class="services-list">
            <? foreach ($services as $service): ?>
                <div class="service-item" id="<?= $service['long_name'] ?>">
                    <p class="service-name"><?= $service['name'] ?></p>
                    <div class="method-list">
                        <? foreach ($service['methods'] as $method): ?>
                            <div class="method-item">
                                <p class="method-name"><?= $method['name'] ?></h2>
                                <div class="method-parameters-list">
                                    <? foreach ($method['parameters'] as $parameter): ?>
                                        <div class="method-parameter-item">
                                            <p class="method-parameter-name"><?= $parameter['name'] ?></p>
                                            <p class="method-parameter-type">
                                                <a href="#<?= $parameter['type'] ?>"><?= $parameter['type'] ?></a>
                                            </p>
                                            <p class="method-parameter-comment"><?= $parameter['comment'] ?? "" ?></p>
                                        </div>
                                    <? endforeach ?>
                                </div>
                                <p class="method-comment"><?= $method['comment'] ?></p>
                            </div>
                        <? endforeach ?>
                    </div>
                </div>
            <? endforeach ?>
        </div>
    </body>
</html>
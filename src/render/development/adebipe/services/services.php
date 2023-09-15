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
        <div class="services-list">
            <? foreach ($services as $service): ?>
                <div class="services-item">
                    <p class="service-name"><?= $service['name'] ?></p>
                    <div class="method-list">
                    <? foreach ($service['methods'] as $method): ?>
                        <div class="method-item">
                            <p class="method-name"><?= $method['name'] ?></h2>
                            <p class="method-comment"><?= $method['comment'] ?></p>
                            <div class="method-parameters">
                                <? foreach ($method['parameters'] as $parameter): ?>
                                    <p class="method-parameter"><?= $parameter['name'] ?>: <?= $parameter['type'] ?></p>
                                <? endforeach ?>
                            </div>
                        </div>
                    <? endforeach ?>
                </div>
            <? endforeach ?>
        </div>
    </body>
</html>
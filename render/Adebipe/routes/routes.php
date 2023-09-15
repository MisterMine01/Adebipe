<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            <?php
                include __DIR__ . '/routes.css';
            ?>
        </style>
    </head>
    <body>
        <div class="summary">
            <div class="summary-item">
                <p class="summary-item-name">Routes</p>
                <div class="summary-item-list">
                    <? foreach ($routes as $route): ?>
                        <a href="#<?= $route['route'] ?>_<?= $route['method'] ?>"><?= $route['route'] ?></a>
                    <? endforeach ?>
                </div>
            </div>
        </div>
        <div class="routes-list">
            <? foreach ($routes as $route): ?>
                <? if (count($route["more"]) > 0): ?>
                    <details class="route-item" id="<?= $route['route'] ?>_<?= $route['method'] ?>">
                        <summary class="route-name"><span class="pastille"><?= $route["method"] ?></span> <?= $route['route'] ?></summary>
                        <div class="more-list">
                            <? if (isset($route["more"]['schema'])): ?>
                                <div class="more-item">
                                    <p class="more-name">schema</p>
                                    <div class="more-detail more-schema">
                                        <pre><?= json_encode($route["more"]['schema'], JSON_PRETTY_PRINT) ?></pre>
                                    </div>
                                </div>
                            <? endif ?>
                        </div>
                    </details>
                <? else: ?>
                    <div class="route-item" id="<?= $route['route'] ?>_<?= $route['method'] ?>">
                        <p class="route-name"><span class="pastille"><?= $route["method"] ?></span> <?= $route['route'] ?></p>
                    </div>
                <? endif ?>
            <? endforeach ?>
        </div>
        
    </body>
</html>
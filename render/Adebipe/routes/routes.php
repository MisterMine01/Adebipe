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
                        <summary class="route-name">
                            <? if (isset($route['env'])): ?>
                                <span class="pastille <?= $route['env'] ?>"><?= $route['env'] ?></span>
                            <? endif ?>
                            <span class="pastille"><?= $route["method"] ?></span>
                            <?= $route['route'] ?>
                        </summary>
                        <div class="more-list">
                            <? foreach ($route["more"] as $key => $value): ?>
                                <div class="more-item">
                                    <? if ($key === 'Adebipe\Annotations\ValidatePost'): ?>
                                        <p class="more-name">schema</p>
                                        <div class="more-detail more-schema">
                                            <pre><?= json_encode($value["schema"], JSON_PRETTY_PRINT) ?></pre>
                                        </div>
                                    <? else: ?>
                                        <p class="more-name"><?= $key ?></p>
                                        <div class="more-detail">
                                            <pre><?= json_encode($value, JSON_PRETTY_PRINT) ?></pre>
                                        </div>
                                    <? endif ?>
                                </div>
                            <? endforeach ?>
                        </div>
                    </details>
                <? else: ?>
                    <div class="route-item" id="<?= $route['route'] ?>_<?= $route['method'] ?>">
                        <p class="route-name">
                            <? if (isset($route['env'])): ?>
                                <span class="pastille <?= $route['env'] ?>"><?= $route['env'] ?></span>
                            <? endif ?>
                            <span class="pastille"><?= $route["method"] ?></span>
                            <?= $route['route'] ?>
                        </p>
                    </div>
                <? endif ?>
            <? endforeach ?>
        </div>
        
    </body>
</html>
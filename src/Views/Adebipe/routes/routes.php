<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            <?php
                require __DIR__ . '/routes.css';
            ?>
        </style>
    </head>
    <body>
        <div class="summary">
            <div class="summary-item">
                <p class="summary-item-name">Routes</p>
                <div class="summary-item-list">
                    <? foreach ($routes as $route): ?>
                        <a href="#<?php echo $route['route'] ?>_<?php echo $route['method'] ?>"><?php echo $route['route'] ?></a>
                    <? endforeach ?>
                </div>
            </div>
        </div>
        <div class="routes-list">
            <? foreach ($routes as $route): ?>
                <? if (count($route["more"]) > 0): ?>
                    <details class="route-item" id="<?php echo $route['route'] ?>_<?php echo $route['method'] ?>">
                        <summary class="route-name">
                            <? if (isset($route['env'])): ?>
                                <span class="pastille <?php echo $route['env'] ?>"><?php echo $route['env'] ?></span>
                            <? endif ?>
                            <span class="pastille"><?php echo $route["method"] ?></span>
                            <?php echo $route['route'] ?>
                        </summary>
                        <div class="more-list">
                            <? foreach ($route["more"] as $key => $value): ?>
                                <div class="more-item">
                                    <? if ($key === 'Adebipe\Annotations\ValidatePost'): ?>
                                        <p class="more-name">schema</p>
                                        <div class="more-detail more-schema">
                                            <pre><?php echo json_encode($value["schema"], JSON_PRETTY_PRINT) ?></pre>
                                        </div>
                                    <? else: ?>
                                        <p class="more-name"><?php echo $key ?></p>
                                        <div class="more-detail">
                                            <pre><?php echo json_encode($value, JSON_PRETTY_PRINT) ?></pre>
                                        </div>
                                    <? endif ?>
                                </div>
                            <? endforeach ?>
                        </div>
                    </details>
                <? else: ?>
                    <div class="route-item" id="<?php echo $route['route'] ?>_<?php echo $route['method'] ?>">
                        <p class="route-name">
                            <? if (isset($route['env'])): ?>
                                <span class="pastille <?php echo $route['env'] ?>"><?php echo $route['env'] ?></span>
                            <? endif ?>
                            <span class="pastille"><?php echo $route["method"] ?></span>
                            <?php echo $route['route'] ?>
                        </p>
                    </div>
                <? endif ?>
            <? endforeach ?>
        </div>
        
    </body>
</html>
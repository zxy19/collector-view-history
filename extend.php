<?php

/*
 * This file is part of xypp/collector-view-history.
 *
 * Copyright (c) 2024 小鱼飘飘.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Xypp\CollectorViewHistory;

use Flarum\Extend;
use Xypp\Collector\Extend\ConditionProvider;

return [
    new Extend\Locales(__DIR__ . '/locale'),
    (new ConditionProvider)
        ->provide(ViewHistoryCondition::class)
];

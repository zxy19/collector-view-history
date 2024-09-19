<?php

namespace Xypp\CollectorViewHistory;

use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Date;
use Xypp\Collector\ConditionDefinition;
use Xypp\Collector\Data\ConditionAccumulation;
use Xypp\Collector\RewardDefinition;
use Ziven\viewHistory\Model\ViewHistory;

class ViewHistoryCondition extends ConditionDefinition
{
    public bool $accumulateAbsolute = true;
    public bool $accumulateUpdate = true;
    public function __construct()
    {
        parent::__construct("view_history", null, "xypp-collector-view-history.forum.condition.view-history");
    }
    public function getAbsoluteValue(\Flarum\User\User $user, ConditionAccumulation $conditionAccumulation): bool
    {
        $maxId = 0;
        ViewHistory::where('user_id', $user->id)->get(['id', 'assigned_at'])
            ->each(function ($item) use (&$conditionAccumulation, &$maxId) {
                $time = Carbon::createFromFormat("Y-m-d H:i:s",$item->assigned_at,"Asia/shanghai")->utc();
                $conditionAccumulation->updateValue($time, 1);
                $maxId = max($maxId, $item->id);
            });
        $conditionAccumulation->updateFlag(strval($maxId));
        return $conditionAccumulation->dirty;
    }

    public function updateValue(\Flarum\User\User $user, ConditionAccumulation $conditionAccumulation): bool
    {
        $maxId = $conditionAccumulation->updateFlag ?? 0;
        $maxId = ViewHistory::where('user_id', $user->id)->where('id', '>', $maxId)->get(['id', 'assigned_at'])
            ->each(function ($item) use (&$conditionAccumulation, &$maxId) {
                $time = Carbon::createFromFormat("Y-m-d H:i:s",$item->assigned_at,"Asia/shanghai")->utc();
                $conditionAccumulation->updateValue($time, 1);
                $maxId = max($maxId, $item->id);
            });
        $conditionAccumulation->updateFlag(strval($maxId));
        return $conditionAccumulation->dirty;
    }
}
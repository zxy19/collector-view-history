<?php

namespace Ziven\viewHistory\Listeners;

use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Discussion\Discussion;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Xypp\Collector\Data\ConditionData;
use Xypp\Collector\Event\UpdateCondition;
use Ziven\viewHistory\Model\ViewHistory;

class DiscussionViewedHandler
{
    private $settings;
    private $events;

    public function __construct(SettingsRepositoryInterface $settings, Dispatcher $events)
    {
        $this->settings = $settings;
        $this->events = $events;
    }

    public function __invoke(ShowDiscussionController $controller, Discussion $discussion, $request, $document)
    {
        $viewHistoryEnable = $request->getAttribute('actor')->getPreference("viewHistoryEnable");

        if ($viewHistoryEnable) {
            $actor = RequestUtil::getActor($request);
            $currentUserID = $actor->id;

            if ($currentUserID) {
                $discussionID = $discussion->id;
                $postID = $discussion->first_post_id;
                $matchCondition = ['user_id' => $currentUserID, 'discussion_id' => $discussionID];

                if ($postID) {
                    if (!ViewHistory::where($matchCondition)->exists()) {
                        $this->events->dispatch(
                            new UpdateCondition(
                                $actor,
                                [new ConditionData('view_history', 1)]
                            )
                        );
                    }
                    ViewHistory::updateOrCreate($matchCondition, ['assigned_at' => Carbon::now('Asia/Shanghai'), 'post_id' => $postID]);
                }
            }
        }
    }
}
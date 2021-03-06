<?php
namespace Caco\Feed;

use Caco\Feed\Model\Feed;
use Caco\Feed\Model\Item;
use Caco\Feed\Model\ItemQueue;
use \Slim\Slim;

/**
 * Class REST
 * @package Caco\Feed
 * @author Guido Krömer <mail 64 cacodaemon 46 de>
 */
class REST
{
    /**
     * @var \Slim\Slim
     */
    protected $app;

    /**
     * @var Manager
     */
    protected $manager;

    public function __construct()
    {
        $this->manager = new Manager;
        $this->manager->setFeedReader(new SimplePieFeedReader);
        $this->app = Slim::getInstance();
    }

    /**
     * POST: /feed
     */
    public function addFeed()
    {
        $data = json_decode($this->app->request()->getBody(), true);
        $feed = $this->manager->addFeed($data['url']);

        if ($feed) {
            $this->app->render(201, ['response' => $feed->id]);
        } else {
            $this->app->render(500);
        }
    }

    /**
     * GET /feed/:id
     *
     * @param int $id
     */
    public function getFeed($id)
    {
        $feed = new Feed;
        if ($feed->read($id)) {
            $this->app->render(200, ['response' => $feed]);
        } else {
            $this->app->render(404);
        }
    }

    /**
     * GET /feed
     */
    public function getAllFeeds()
    {
        $this->app->render(200, ['response' => (new Feed)->all()]);
    }

    /**
     * DELETE /feed/:id
     *
     * @param int $id
     */
    public function deleteFeed($id)
    {
        $this->app->render($this->manager->deleteFeed($id) ? 200 : 404, ['response' => $id]);
    }

    /**
     * PUT /feed/:id
     *
     * @param int $id
     */
    public function editFeed($id)
    {
        $feed = new Feed;
        if ($feed->read($id)) {
            $feed->setArray(json_decode($this->app->request()->getBody(), true));

            $this->app->render($feed->save() ? 200 : 500, ['response' => $feed->id]);
        } else {
            $this->app->render(404);
        }
    }

    /**
     * GET /feed/item
     */
    public function getAllItems()
    {
        $this->app->render(200, ['response' => $this->manager->getAllItems()]);
    }

    /**
     * GET /feed/:id/item
     *
     * @param int $id
     */
    public function getItems($id)
    {
        $this->app->render(200, ['response' => (new Item)->readItems($id)]);
    }

    /**
     * GET /feed/item/:id
     *
     * @param int $id
     */
    public function getItem($id)
    {
        $item = new Item;
        if ($item->read($id)) {
            $this->app->render(200, ['response' => $item]);

            if (!$item->read) {
                $item->read = 1;
                $item->save();
            }
        } else {
            $this->app->render(404, ['response' => $id]);
        }
    }

    /**
     * DELETE /feed/item:id
     *
     * @param $id
     */
    public function deleteItem($id)
    {
        $item = new Item;
        if ($item->read($id)) {
            $this->app->render($item->delete() ? 200 : 500, ['response' => $item]);
        } else {
            $this->app->render(404, ['response' => $id]);
        }
    }

    /**
     * GET /feed/update
     */
    public function updateAllFeeds()
    {
        $this->app->render(200, ['response' => $this->manager->updateAllFeeds()]);
    }

    /**
     * GET /feed/update/:id
     *
     * @param int $id
     */
    public function updateFeed($id)
    {
        $feed = new Feed;
        if (!$feed->read($id)) {
            $this->app->render(404, ['response' => $id]);

            return;
        }

        $this->app->render(200, ['response' => $this->manager->updateFeed($feed)]);
    }

    /**
     * GET /feed/calculate-update-interval
     */
    public function calculateUpdateInterval()
    {
        $this->app->render(200, ['response' => $this->manager->calculateUpdateInterval()]);
    }

    /**
     * POST /feed/item/queue/:id
     *
     * @param $id
     */
    public function enqueueItem($id)
    {
        $item = new Item;

        if (!$item->read($id)) {
            $this->app->render(404, ['response' => $id]);

            return;
        }

        if ((new ItemQueue)->enqueue($id)) {
            $this->app->render(201, ['response' => $id]);
        } else {
            $this->app->render(500);
        }
    }

    /**
     * GET /feed/item/queue
     */
    public function dequeueItem()
    {
        $itemQueue = new ItemQueue;

        if ($itemQueue->dequeue()) {
            $this->getItem($itemQueue->id_item);
        } else {
            $this->app->render(404);
        }
    }
}
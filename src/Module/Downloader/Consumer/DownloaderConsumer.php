<?php
namespace Yurun\Crawler\Module\Downloader\Consumer;

use Imi\App;
use Imi\Bean\Annotation\Bean;
use Imi\Queue\Contract\IMessage;
use Imi\Util\Http\ServerRequest;
use Imi\Queue\Driver\IQueueDriver;
use Imi\Queue\Service\BaseQueueConsumer;
use Yurun\Crawler\Module\Downloader\Model\DownloadParams;
use Yurun\Crawler\Module\Downloader\Model\DownloadMessage;

/**
 * 下载器消费者
 * 
 * @Bean("DownloaderConsumer")
 */
class DownloaderConsumer extends BaseQueueConsumer
{
    /**
     * 处理消费
     * 
     * @param \Imi\Queue\Contract\IMessage $message
     * @param \Imi\Queue\Driver\IQueueDriver $queue
     * @return void
     */
    protected function consume(IMessage $message, IQueueDriver $queue)
    {
        // 下载消息处理
        $downloadMessage = new DownloadMessage;
        $downloadMessage->loadFromJsonString($message->getMessage());
        /** @var \Yurun\Crawler\Module\Crawler\Contract\BaseCrawler $crawler */
        $crawler = App::getBean($downloadMessage->crawler);
        /** @var \Yurun\Crawler\Module\Crawler\Contract\BaseCrawlerItem $crawlerItem */
        $crawlerItem = App::getBean($downloadMessage->crawlerItem);
        $downloadParams = new DownloadParams;
        $downloadParams->data = $downloadMessage->data;
        // 构建请求对象
        $downloadParams->request = new ServerRequest($downloadMessage->url, $downloadMessage->headers, $downloadMessage->body, $downloadMessage->method);
        // 下载
        $response = $crawlerItem->download($downloadParams);
        // 推送解析器消息
        $crawler->pushParserMessage($downloadMessage->crawlerItem, $response, $downloadParams->data);
        $queue->success($message);
    }

}

<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extension\Exception;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\Utils;

/**
 * Sends notifications through Slack Webhooks
 *
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @see    https://api.slack.com/incoming-webhooks
 */
class DingTalkHandler extends AbstractProcessingHandler
{
    /**
     * Slack Webhook token
     * @var string
     */
    private $webhookUrl = 'https://oapi.dingtalk.com/robot/send';

    private $accessToken = '478cdb536f9ce3d96b942e990c61137afd8b95d1d9306bd75272adb73060d338';

    private $secret = 'SEC644533a0147a373e11dc3c6d9807a1194892a6e7e5cf15b41f9e2d08745f311a';

    /**
     * @param string      $webhookUrl             Slack Webhook URL
     * @param string|null $channel                Slack channel (encoded ID or name)
     * @param string|null $username               Name of a bot
     * @param bool        $useAttachment          Whether the message should be added to Slack as attachment (plain text otherwise)
     * @param string|null $iconEmoji              The emoji name to use (or null)
     * @param bool        $useShortAttachment     Whether the the context/extra messages added to Slack as attachments are in a short style
     * @param bool        $includeContextAndExtra Whether the attachment should include context and extra data
     * @param string|int  $level                  The minimum logging level at which this handler will be triggered
     * @param bool        $bubble                 Whether the messages that are handled can bubble up the stack or not
     * @param array       $excludeFields          Dot separated list of fields to exclude from slack message. E.g. ['context.field1', 'extra.field2']
     */
    public function __construct(
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $record
     */
    protected function write(array $record): void
    {
        $postData = $this->getData($record);
        $postString = Utils::jsonEncode($postData);

        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->getWebhookUrl(),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Content-type: application/json'),
            CURLOPT_POSTFIELDS => $postString,
        );
        if (defined('CURLOPT_SAFE_UPLOAD')) {
            $options[CURLOPT_SAFE_UPLOAD] = true;
        }

        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        if ($res === false) {
            throw new \Exception('cURL resource: ' . (string)$ch . '; cURL error: ' . curl_error($ch) . ' (' . curl_errno($ch) . ')');
        }
        curl_close($ch);
        //Curl\Util::execute($ch);
    }


    public function getWebhookUrl(): string
    {
        $timestamp = time() * 1000;
        $sign = urlencode(base64_encode(hash_hmac('sha256', $timestamp . PHP_EOL . $this->secret, $this->secret, true)));
        return $this->webhookUrl . "?access_token={$this->accessToken}&timestamp={$timestamp}&sign={$sign}";
    }


    private function getData(array $record):array
    {
        return [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $record['level_name'],
                'text' => "### {$record['level_name']}" . PHP_EOL .
                    "> #### {$record['message']}" . PHP_EOL .
                    "> " . (empty($record['context']) ? '' : (is_string($record['context']) ? $record['context'] : json_encode($record['context']))) . PHP_EOL .
                    "> " . json_encode($record['extra']) . PHP_EOL .
                    "> ###### {$record['datetime']}"
            ],
        ];
    }

}

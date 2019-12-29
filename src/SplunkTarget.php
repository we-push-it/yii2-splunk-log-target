<?php
/**
 * @link https://www.moaas.de/
 * @copyright Copyright (c) 2019 moaas GmbH
 * @license MIT
 */

namespace wepushit\log;
use Yii;
use yii\log\Logger;
use yii\log\Target;

/**
 * SplunkTarget sends logs to httpcollector from splunk enterprise.
 *
 * @author moaas GmbH <hello@moaas.de>
 * @since 2.0
 */
class SplunkTarget extends Target
{

    /**
     * @inheritdoc
     */
    public function export()
    {

        $splunkmsg = [
            'source' => Yii::$app->params['splunk']['source'],
            'host' => Yii::$app->params['splunk']['host'],
            'sourcetype' => Yii::$app->params['splunk']['sourcetype'],
            'event' => '',
        ];


        foreach ($this->messages as $key => $message) {

            $splunkmsg["event"] = $this->formatMessage($message);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::$app->params['splunk']['url']);
            curl_setopt($ch, CURLOPT_USERPWD, Yii::$app->params['splunk']['token']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Skip SSL Verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($splunkmsg));

            $output = curl_exec($ch);
            if($output === false)
            {
                Yii::error('Curl-Fehler: ' . curl_error($ch));
                Yii::error('Curl-Fehlernr: ' . curl_errno($ch));
            }
            curl_close($ch);
        }
    }

    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string) $text;
            }
        }
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        $prefix = $this->getMessagePrefix($message);

        return [
            'timestamp' => $this->getTime($timestamp),
            'prefix' => $prefix,
            'level' => $level,
            'category' => $category,
            'text' => $text,
            'traces' => empty($traces) ? '' : $traces,
        ];
    }
}
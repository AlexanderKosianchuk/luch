<?php

namespace Component;

use Exception;

class RealtimeEventComponent extends BaseComponent
{
  public function process($fdrId, $tableName, $frameNum, $prevEventResults, $link = null)
  {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);
    $events = $fdr->getRealtimeEvents();
    $eventResults = [];

    foreach ($events as $eventObj) {
      $event = $eventObj->get();
      $query = str_replace("[table]", $tableName, $event['alg']);
      $result = $link->query($query);

      if (!$result) {
        error_log('REALTIME_EVENT error. Id: '.$eventObj->getId().'. Description: '. $link->error);
      }

      if ($row = $result->fetch_array()) {
        if (!isset($row[0])) {
          continue;
        }

        $newEventValue = $row[0];
        $prevEvents = $this->filterById($eventObj->getId(), $prevEventResults);
        $prevValue = null;

        if (count($prevEvents)
          && isset($prevEvents[0]['value'])
          && (intval($prevEvents[0]['frameNum']) === $frameNum - 1)
        ) {
          $prevValue = $prevEvents[0]['value'];
        }

        $aggregationRes = $this->agregate(
          $newEventValue,
          $prevValue,
          $event['stresshold'],
          $event['func']
        );

        if ($aggregationRes === null) {
          $eventResults = array_merge($eventResults, $prevEvents);
          continue;
        }

        if (count($prevEvents)
          && isset($prevEvents[0]['frameNum'])
        ) {
          if (intval($prevEvents[0]['frameNum']) === $frameNum - 1) {
            $prevEvents[0]['frameNum'] = $frameNum;
            $prevEvents[0]['value'] = $aggregationRes;
          } else {
            $eventResults[] = [
              'eventId' => $event['id'],
              'event' => $event,
              'value' => $aggregationRes,
              'frameNum' => $frameNum
            ];
          }

          $eventResults = array_merge($eventResults, $prevEvents);
          continue;
        }

        $eventResults[] = [
          'eventId' => $event['id'],
          'event' => $event,
          'value' => $aggregationRes,
          'frameNum' => $frameNum
        ];
      }

      $result->free();
    }

    return $eventResults;
  }

  public function filterById($eventId, $prevEventResults)
  {
    $prevEvents = [];
    foreach ($prevEventResults as $item) {
      if ($item['eventId'] === $eventId) {
        $prevEvents[] = $item;
      }
    }

    $sort = function ($a, $b) {
      $a = $a['value'];
      $b = $b['value'];

      if ($a == $b) return 0;
      return ($a > $b) ? -1 : 1;
    };

    return $prevEvents;
  }

  public function agregate($value, $prevEventValue, $stresshold, $func)
  {
    if (strpos ($stresshold, '<', 0) === 0) {
      $stresshold = floatval(str_replace('<', '', $stresshold));
      if ($value > $stresshold) {
        return null;
      }
    } else {
      $stresshold = floatval(str_replace('>', '', $stresshold));
      if ($value < $stresshold) {
        return null;
      }
    }

    switch ($func) {
      case 'MIN':
        if ($prevEventValue === null) {
          return $value;
        } else {
          return min($value, $prevEventValue);
        }
      break;
      case 'MAX':
        if ($prevEventValue === null) {
          return $value;
        } else {
          return max($value, $prevEventValue);
        }
      break;
      case 'AVG':
        if ($prevEventValue === null) {
          return $value;
        } else {
          return ($value + $prevEventValue) / 2;
        }
      break;
      case 'SUM':
        if ($prevEventValue === null) {
          return $value;
        } else {
          return ($value + $prevEventValue);
        }
      break;
      case 'COUNTER':
      /* is the same that counter but view know that stopwatch */
      /* shoud be output as 00:00:00 */
      case 'STOPWATCH':
        if ($prevEventValue === null) {
          return 0;
        } else {
          return $prevEventValue + 1;
        }
      break;
    }

    return null;
  }
}

<?php

namespace Component;

use Exception;

class RealtimeEventComponent extends BaseComponent
{
  public function process($fdrId, $tableName, $prevEventResults, $link = null)
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

        $prevVal = $this->findPrevVal($eventObj->getId(), $prevEventResults);
        $aggregationRes = $this->agregate($row[0], $prevVal, $event['stresshold'], $event['func']);

        if ($aggregationRes !== null) {
          $eventResults[] = [
            'event' => $event,
            'result' => $aggregationRes
          ];
        }
      }

      $result->free();
    }

    return $eventResults;
  }

  public function agregate($value, $prevVal, $stresshold, $func)
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
        if ($prevVal === null) {
          return $value;
        } else {
          return min($value, $prevVal);
        }
      break;
      case 'MAX':
        if ($prevVal === null) {
          return $value;
        } else {
          return max($value, $prevVal);
        }
      break;
      case 'AVG':
        if ($prevVal === null) {
          return $value;
        } else {
          return ($value + $prevVal) / 2;
        }
      break;
      case 'SUM':
        if ($prevVal === null) {
          return $value;
        } else {
          return ($value + $prevVal);
        }
      break;
      case 'COUNTER':
      /* is the same that counter but view know that stopwatch */
      /* shoud be output as 00:00:00 */
      case 'STOPWATCH':
        if ($prevVal === null) {
          return 0;
        } else {
          return $prevVal + 1;
        }
      break;
    }

    return null;
  }

  public function findPrevVal($eventId, $prevEventResults)
  {
    foreach ($prevEventResults as $item) {
      if ($item['eventId'] === $eventId) {
        return $item['value'];
      }
    }

    return null;
  }
}

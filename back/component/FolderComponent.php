<?php

namespace Component;

use Exception;

class FolderComponent extends BaseComponent
{
  /**
   * @Inject
   * @var Component\FlightComponent
   */
  private $flightComponent;

  public function deleteFolderContent($folderId)
  {
    $content = $this->getUserContent();
    $tree = $this->buildTree($content);

    $searchedBranch = [];
    $this->getBranch($tree, $folderId, $searchedBranch);

    $leafes = [ $searchedBranch ];
    if (isset($searchedBranch['children'])) {
      $this->getBranchItems($searchedBranch['children'], $leafes);
    }

    if (!$this->rbac()->check('deleteFolderContent')) {
      $folder = $this->em()
        ->getRepository('Entity\Folder')
        ->delete($folderId, $userId);

      return;
    }

    $userId = $this->user()->getId();

    foreach ($leafes as $item) {
      if (isset($item['id'])) {
        $id = intval($item['id']);
        if ($item['type'] === 'folder') {
          $folder = $this->em()
            ->getRepository('Entity\Folder')
            ->delete($id, $userId);
        } else if (($item['type'] === 'flight')
          && $this->rbac()->check('deleteFlight')
        ) {
          $this->flightComponent->deleteFlight($id, $userId);
        }
      }
    }

    return $leafes;
  }

  public function getUserContent($userId = null)
  {
    $userId = isset($userId) ? $userId : $this->user()->getId();

    $folders = $this->em()
      ->getRepository('Entity\Folder')
      ->findBy([ 'userId' => $userId ]);

    if (!$folders) {
      return null;
    }

    $available = [];
    foreach ($folders as $folder) {
      $available[] = [
        'id' => $folder->getId(),
        'text' => $folder->getName(),
        'type' => 'folder',
        'parent' => $folder->getPath()
      ];
    }

    $flightsToFolders = $this->em()
      ->getRepository('Entity\FlightToFolder')
      ->findBy([
        'userId' => $userId
      ]);

    foreach ($flightsToFolders as $flightToFolder) {
      $flight = $flightToFolder->getFlight();
      $fdr = $flight->getFdr();

      $name = implode(', ', [
        $flight->getBort(),
        $flight->getVoyage(),
        $flight->getCaptain(),
        date('d/m/y H:i', $flight->getStartCopyTime()),
        $fdr->getName(),
        $flight->getDepartureAirport(),
        $flight->getArrivalAirport()
      ]);

      $available[] = array(
        // to make imposible flight and folder id dublication * -1
        'id' => $flight->getId() * (-1),
        'text' => $name,
        'type' => 'flight',
        'parent' => $flightToFolder->getFolderId()
      );
    }

    return $available;
  }

  private function buildTree($d, $r = 0, $pk = 'parent', $k = 'id', $c = 'children')
  {
    $m = array();
    foreach ($d as $e) {
      isset($m[$e[$pk]]) ?: $m[$e[$pk]] = array();
      isset($m[$e[$k]]) ?: $m[$e[$k]] = array();
      $m[$e[$pk]][] = array_merge($e, array($c => &$m[$e[$k]]));
    }
    return $m[$r];// add [0] if there couldnot be more than one root node
  }

  private function getBranch($tree, $branchId, &$searchedBranch)
  {
    foreach ($tree as $branch) {
      if (intval($branch['id']) === intval($branchId)) {
        $searchedBranch = $branch;
        break;
      }
      if (count($branch['children']) > 0) {
        $this->getBranch($branch['children'], $branchId, $searchedBranch);
      }
    }
  }

  private function getBranchItems($branch, &$items)
  {
    foreach ($branch as $leaf) {
      $items[] = $leaf;
      if (count($leaf['children']) > 0) {
        $this->getBranchItems($leaf['children'], $items);
      }
    }
  }
}

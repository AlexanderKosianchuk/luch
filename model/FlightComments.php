<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class FlightComments
{
    private $table = 'flight_comments';

    public function getComment($flightId)
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Int expected. Passed: "
                . json_encode($flightId), 1);
        }

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $q = "SELECT `id`, `comment`, `commander-admitted`, `aircraft-allowed`, `general-admission`, `id_user`, `dt` "
            . "FROM `".$this->table."` WHERE `id_flight` = ? LIMIT 1;";
        $stmt = $link->prepare($q);
        $stmt->bind_param("i", $flightId);
        $stmt->execute();
        $result = $stmt->get_result();

        $comment = [];
        if($row = $result->fetch_array()) {
            foreach ($row as $key => $value) {
                $comment[$key] = $value;
            }
        }

        if (!isset($comment['commander-admitted'])) {
            $comment['commander-admitted'] = 0;
        }

        if (!isset($comment['aircraft-allowed'])) {
            $comment['aircraft-allowed'] = 0;
        }

        if (!isset($comment['general-admission'])) {
            $comment['general-admission'] = 0;
        }

        if (!isset($comment['comment'])) {
            $comment['comment'] = '';
        }

        $c->Disconnect();
        unset($c);

        return $comment;
    }

    public function insertComment($flightId,
        $userId,
        $comment,
        $commanderAdmitted,
        $aircraftAllowed,
        $generalAdmission
    ) {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Int expected. Passed: "
                . json_encode($flightId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        if (!is_string($comment)) {
            throw new Exception("Incorrect comment passed. String expected. Passed: "
                . json_encode($comment), 1);
        }

        if (($commanderAdmitted !== 1) && ($commanderAdmitted !== 0)) {
            throw new Exception("Incorrect commanderAdmitted passed. 0 or 1 expected. Passed: "
                . json_encode($commanderAdmitted), 1);
        }

        if (($aircraftAllowed !== 1) && ($aircraftAllowed !== 0)) {
            throw new Exception("Incorrect aircraftAllowed passed. 0 or 1 expected. Passed: "
                . json_encode($aircraftAllowed), 1);
        }

        if (($generalAdmission !== 1) && ($generalAdmission !== 0)) {
            throw new Exception("Incorrect generalAdmission passed. 0 or 1 expected. Passed: "
                . json_encode($generalAdmission), 1);
        }

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $q = "INSERT INTO `".$this->table."` "
            . "(`id_flight`, `id_user`, `comment`, `commander-admitted`, `aircraft-allowed`, `general-admission`) "
            . "VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $link->prepare($q);
        $stmt->bind_param("iisiii", $flightId,
            $userId,
            $comment,
            $commanderAdmitted,
            $aircraftAllowed,
            $generalAdmission
        );
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);

        return true;
    }

    public function updateComment($flightId,
        $userId,
        $comment,
        $commanderAdmitted,
        $aircraftAllowed,
        $generalAdmission
    ) {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Int expected. Passed: "
                . json_encode($flightId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        if (!is_string($comment)) {
            throw new Exception("Incorrect comment passed. String expected. Passed: "
                . json_encode($comment), 1);
        }

        if (($commanderAdmitted !== 1) && ($commanderAdmitted !== 0)) {
            throw new Exception("Incorrect commanderAdmitted passed. 0 or 1 expected. Passed: "
                . json_encode($commanderAdmitted), 1);
        }

        if (($aircraftAllowed !== 1) && ($aircraftAllowed !== 0)) {
            throw new Exception("Incorrect aircraftAllowed passed. 0 or 1 expected. Passed: "
                . json_encode($aircraftAllowed), 1);
        }

        if (($generalAdmission !== 1) && ($generalAdmission !== 0)) {
            throw new Exception("Incorrect generalAdmission passed. 0 or 1 expected. Passed: "
                . json_encode($generalAdmission), 1);
        }

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $q = "UPDATE `".$this->table."` "
            . "SET `id_user` = ?, `comment` = ?, `commander-admitted` = ?, `aircraft-allowed` = ?, `general-admission` = ? "
            . "WHERE `id_flight` = ?";
        $stmt = $link->prepare($q);
        $stmt->bind_param("isiiii",
            $userId,
            $comment,
            $commanderAdmitted,
            $aircraftAllowed,
            $generalAdmission,
            $flightId
        );

        $results = $stmt->execute();

        $responce = true;
        if (!$results){
            $responce = 'Error : ('. $link->errno .') '. $link->error;
        }

        $stmt->close();

        $c->Disconnect();
        unset($c);

        return $responce;
    }

    public function putComment($flightId,
        $userId,
        $comment,
        $commanderAdmitted,
        $aircraftAllowed,
        $generalAdmission
    ) {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Int expected. Passed: "
                . json_encode($flightId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        if (!is_string($comment)) {
            throw new Exception("Incorrect comment passed. String expected. Passed: "
                . json_encode($comment), 1);
        }

        if (($commanderAdmitted !== 1) && ($commanderAdmitted !== 0)) {
            throw new Exception("Incorrect commanderAdmitted passed. 0 or 1 expected. Passed: "
                . json_encode($commanderAdmitted), 1);
        }

        if (($aircraftAllowed !== 1) && ($aircraftAllowed !== 0)) {
            throw new Exception("Incorrect aircraftAllowed passed. 0 or 1 expected. Passed: "
                . json_encode($aircraftAllowed), 1);
        }

        if (($generalAdmission !== 1) && ($generalAdmission !== 0)) {
            throw new Exception("Incorrect generalAdmission passed. 0 or 1 expected. Passed: "
                . json_encode($generalAdmission), 1);
        }

        $flightComment = $this->getComment($flightId);

        if (!isset($flightComment['id'])) {
            $this->insertComment($flightId,
                $userId,
                $comment,
                $commanderAdmitted,
                $aircraftAllowed,
                $generalAdmission
            );
        } else {
            $this->updateComment($flightId,
                $userId,
                $comment,
                $commanderAdmitted,
                $aircraftAllowed,
                $generalAdmission
            );
        }

        return true;
    }
}

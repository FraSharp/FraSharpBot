<?php

function saveNote($chatid, $noteName, $noteText) {
    $conn = new mysqli("host", "user", "password", "frasharpbot");
    $query = "SELECT * FROM `frasharpbot`.`notes` WHERE `note_name` = '$noteName'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();

    if ($row['group_id'] == $chatid and $row['note_name'] == $noteName) {
        $deleteThatNote = "DELETE FROM `frasharpbot`.`notes` WHERE `group_id` = '$chatid' AND `note_name` = '$noteName'";
        $deleteQuery = $conn->query($deleteThatNote);

        $insert = "INSERT INTO `frasharpbot`.`notes` (`group_id`, `note_name`, `note_text`) VALUES ('$chatid', '$noteName', '$noteText')";
        $conn->query($insert);

        return $message = "$noteName updated";
    }

    if ($noteName == NULL or $noteText == NULL or $noteName == "" or $noteName == " " or $noteText == "" or $noteText == " ") {
        return $message = "can't save such note";
    } else {
        $insert = "INSERT INTO `frasharpbot`.`notes` (`group_id`, `note_name`, `note_text`) VALUES ('$chatid', '$noteName', '$noteText')";
        $conn->query($insert);
        if ($conn->error) {
            return $message = "error occured: $conn->error";
        } else {
            return $message = "$noteName added";
        }
    }
}


function getNote($chatid, $noteName) {
    $conn = new mysqli("host", "user", "password", "frasharpbot");
    $Notequery = "SELECT * FROM `frasharpbot`.`notes` WHERE `note_name` = '$noteName'";
    $Noteresult = $conn->query($Notequery);
    $noteRow = $Noteresult->fetch_assoc();

    if ($noteRow['group_id'] == $chatid) {
        return $message = $noteRow['note_text'];
    } else {
        if ($noteRow['group_id'] != $chatid) {
            return $message = "note <code>$noteName</code> not found";
        }
    }
}


function removeNote($chatid, $noteName) {
    $conn = new mysqli("host", "user", "password", "frasharpbot");
    $Notequery = "SELECT * FROM `frasharpbot`.`notes` WHERE `note_name` = '$noteName'";
    $Noteresult = $conn->query($Notequery);
    $noteRow = $Noteresult->fetch_assoc();

    if ($noteRow['group_id'] == $chatid) {
        $delete = "DELETE FROM `frasharpbot`.`notes` WHERE `note_name` = '$noteName'";
        $deleteQuery = $conn->query($delete);
        if ($deleteQuery) {
            return $message = "$noteName deleted";
        } else {
            if (!$deleteQuery) {
                return $message = "$noteName not deleted, $conn->error";
            }
        }
    } else {
        if ($noteRow['group_id'] != $chatid) {
            return $message = "note to delete <code>$noteName</code> not found";
        }
    }
}
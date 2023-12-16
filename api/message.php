<?php

class Messages
{
    // Message
    public function StatusMessage($status, $type, $message, $data = null)
    {
        echo json_encode([
            'status' => $status,
            'type' => $type,
            'message' => $message,
            'data' => $data
        ]);
    }
}
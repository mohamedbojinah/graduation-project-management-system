<?php
function getReceivedMessages($user_id, $conn) {
    $stmt = $conn->prepare("SELECT messages.*, user.name AS sender_name 
                            FROM messages 
                            JOIN user ON messages.sender_id = user.id 
                            WHERE receiver_id = ? 
                            ORDER BY sent_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

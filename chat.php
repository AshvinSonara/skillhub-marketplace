<?php
require_once __DIR__ . '/../includes/auth_check.php';
$requiredRole = 'freelancer';
require_once __DIR__ . '/../includes/chat_shared.php';

$pageTitle = "Chat";
include __DIR__ . '/../includes/header.php';
?>

<!-- Page auto-refreshes every 8 seconds so new messages appear without needing AJAX/fetch
     (some free hosts block fetch()-based polling, so a normal page reload is used instead) -->
<meta http-equiv="refresh" content="8">

<h1>Chat with <?php echo clean($otherPartyName); ?></h1>
<p class="small-text">
    Regarding order: <?php echo clean($order['title']); ?>
    &nbsp;|&nbsp;
    <a href="freelancer/chat.php?order_id=<?php echo $orderId; ?>">Refresh now</a>
    &nbsp;<span class="small-text">(page auto-refreshes every few seconds)</span>
</p>

<div class="chat-box" id="chat-box">
    <?php if ($chatMessages->num_rows === 0): ?>
        <p class="small-text">No messages yet. Say hello!</p>
    <?php endif; ?>
    <?php while ($m = $chatMessages->fetch_assoc()): ?>
        <div class="chat-msg <?php echo $m['sender_id'] == $userId ? 'me' : ''; ?>">
            <div class="bubble"><?php echo clean($m['message_text']); ?></div>
            <div class="meta"><?php echo date('d M, h:i A', strtotime($m['sent_at'])); ?></div>
        </div>
    <?php endwhile; ?>
</div>

<form method="POST" action="freelancer/chat.php?order_id=<?php echo $orderId; ?>" class="chat-send-row">
    <input type="text" name="message_text" placeholder="Type a message..." required autocomplete="off" autofocus>
    <button type="submit" class="btn">Send</button>
</form>

<script>
// Just scrolls the chat box to the latest message on load - no network requests involved.
var chatBox = document.getElementById('chat-box');
if (chatBox) { chatBox.scrollTop = chatBox.scrollHeight; }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

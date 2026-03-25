<?php
/**
 * Reusable Modal Template Component
 * Usage: include this file with required variables set
 * 
 * Required variables:
 * - $modal_id: string - Unique ID for the modal
 * - $modal_icon: string - Icon emoji or HTML
 * - $modal_title: string - Modal title
 * - $modal_subtitle: string - Modal subtitle (optional)
 * - $modal_content: string - HTML content for modal body
 * - $close_text: string - Text for close button
 */

if (!isset($modal_id) || !isset($modal_title)) {
    return;
}
?>

<div id="<?= htmlspecialchars($modal_id) ?>" class="modal">
    <div class="modal-header">
        <?php if (isset($modal_icon)): ?>
            <div class="modal-icon"><?= $modal_icon ?></div>
        <?php endif; ?>
        
        <h2 class="modal-title"><?= htmlspecialchars($modal_title) ?></h2>
        
        <?php if (isset($modal_subtitle)): ?>
            <p class="modal-subtitle"><?= htmlspecialchars($modal_subtitle) ?></p>
        <?php endif; ?>
    </div>

    <div class="modal-content">
        <?= $modal_content ?>
    </div>

    <div class="modal-footer">
        <button onclick="<?= isset($close_function) ? htmlspecialchars($close_function) : "hide{$modal_id}()" ?>" 
                class="btn btn-primary">
            <i class="fas fa-times"></i>
            <?= isset($close_text) ? htmlspecialchars($close_text) : 'Close' ?>
        </button>
    </div>
</div>

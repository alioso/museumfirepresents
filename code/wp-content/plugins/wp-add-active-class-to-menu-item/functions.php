<?php
if (!defined('ABSPATH'))
    exit;

function wp_add_active_class_to_menu_item_settings() {

    $flag = 0;
    if (isset($_POST['save_class']) && isset($_POST['class_names']) && !empty($_POST['class_names'])) {
        if (update_option('wp_add_active_class_to_menu_item_classes', sanitize_text_field($_POST['class_names']))) {
            $flag = 1;
        } else {
            $flag = -1;
        }
    }

    $classes = esc_html(get_option('wp_add_active_class_to_menu_item_classes'));
    if (empty($classes)) {
        $classes = 'active';
    }
    ?>
    <div class="wrap">
        <h1>WP Add Active Class To Menu Item</h1>
        <?php if ($flag != 0) { ?>
            <div class="error notice" style="<?php echo ($flag == -1) ? 'display:block' : 'display:none' ?>">
                <p><?php _e('There is some problem, please try again later', AMM_WPACTMI_SLUG); ?></p>
            </div>
            <div class="updated notice" style="<?php echo ($flag == 1) ? 'display:block' : 'display:none' ?>">
                <p><?php _e('Classes successfully saved', AMM_WPACTMI_SLUG); ?></p>
            </div>
        <?php } ?>

        <form method="post">
            <p>
                <label><?php _e('Add Custom Class Names', 'wp_add_active_class_to_menu_item'); ?></label>
                <i>(Add comma separated classes for the current active menu)</i><br>
                <textarea id="class_names" rows="3" cols="25" name="class_names" class="form-input"><?php echo $classes; ?></textarea>
            </p>
            <p>
                <input class="button button-large button-primary btn-save" type="submit" name="save_class" value="Save" />
            </p>
        </form>
    </div>
    <?php
}
?>
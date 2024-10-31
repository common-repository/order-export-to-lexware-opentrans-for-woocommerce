<div class="wrap">
<div id="upper_area_">
<h1 id="nsc_bar_admin_title"><?php _e($configs->settings_page_configs->page_title, 'order-to-lexware-nscwto')?></h1>
<p><?php _e($configs->settings_page_configs->description, 'order-to-lexware-nscwto')?></p>
</div>
<h2 class="nav-tab-wrapper">
<?php
//tabs are created
foreach ($configs->setting_page_fields->tabs as $tab) {
    $activeTab = "";
    if ($tab->active === true) {
        $activeTab = 'nav-tab-active';
    }
    printf(
        '<a href="?page=' . $configs->plugin_slug . '&tab=' . $tab->tab_slug . '" class="nav-tab ' . $activeTab . '" >%s</a>',
        $tab->tabname
    );
}
$active_tab_index = $configs->setting_page_fields->active_tab_index;
?>
</h2>
<span id="nsc_settings_content">
<p>
<?php echo wp_kses_post($configs->setting_page_fields->tabs[$active_tab_index]->tab_description) ?>
</p>
<form action="" method="post">
<?php
settings_fields($configs->plugin_slug . $configs->setting_page_fields->tabs[$active_tab_index]->tab_slug);
?>
<?php if ($configs->setting_page_fields->tabs[$active_tab_index]->button_position === "top") {
    submit_button($configs->setting_page_fields->tabs[$active_tab_index]->button_save_text);
}?>

<table class="form-table">
<?php foreach ($configs->setting_page_fields->tabs[$active_tab_index]->tabfields as $field_configs) {?>
 <tr id="tr_<?php echo esc_attr($field_configs->field_slug) ?>">
  <th scope="row">
    <?php echo esc_html($field_configs->name) ?>
  </th>
  <td>
    <fieldset>
<?php

    $multiselectIds = array();
    if ($field_configs->type === "multiselect") {
        $multiselectIds[] = "ff_" . esc_attr($configs->plugin_prefix . $field_configs->field_slug);
    }

    $html = new DOMDocument();
    $html->loadHTML($form_fields->return_form_field($field_configs, $configs->plugin_prefix));
    echo $html->saveHTML();
    ?>
     <p class="description"><?php echo wp_kses_post($field_configs->helpertext) ?></p>
    </fieldset>
  </td>
 </tr>
<?php }?>

<?php if ($configs->setting_page_fields->tabs[$active_tab_index]->button_position === "bottom") {?>
<tr id="tr_save_button">
  <th scope="row"></th>
  <td>
    <fieldset>
      <?php submit_button($configs->setting_page_fields->tabs[$active_tab_index]->button_save_text);?>
    </fieldset>
  </td>
 </tr>
 <?php }?>

</table>
</form>
</span>
<script>
addEventListener('DOMContentLoaded', (event) => {
  var multiselectIds = <?php echo json_encode($multiselectIds); ?>;
  for(var i = 0; i < multiselectIds.length; i+=1){
    let selectBox = new vanillaSelectBox("#" + multiselectIds[i],{search:true});
  }

});
</script>


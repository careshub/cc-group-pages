<?php /* Markup for a single page object when inserted in the DOM */
$access_levels = array(
    1 => array( 'bp_level' => 'anyone', 'label' => 'Anyone' ),
    2 => array( 'bp_level' => 'loggedin', 'label' => 'Logged-in Site Members' ),
    3 => array( 'bp_level' => 'member', 'label' => 'Hub Members' ),
    4 => array( 'bp_level' => 'mod', 'label' => 'Hub Moderators' ),
    5 => array( 'bp_level' => 'admin', 'label' => 'Hub Administrators' ),
    );
?>
<script type="text/html" id="tmpl-ccgp-page">
    <li class="draggable" id="post-{{data.post_id}}">
    <span class="arrow-up"></span><span class="arrow-down"></span>Title: {{data.post_title}} <a href="#" class="toggle-details-pane">Edit</a>
        <div class="details-pane">
            <label for="ccgp-page-{{data.post_id}}-title" >Page Title</label>
            <input type="text" id="ccgp-page-{{data.post_id}}-title" name="ccgp-pages[{{data.post_id}}][title]" value="{{data.post_title}} "/>
            <div class="page-visibility-control">
                <label for="ccgp-page-{{data.post_id}}-visibility">Access</label>
                <select name="ccgp-pages[{{data.post_id}}][visibility]" id="ccgp-page-{{data.post_id}}-visibility" class="page-visibility">
                    <?php foreach ( $access_levels as $key => $value ) { ?>
                        <option value="<?php echo $value['bp_level'] ?>" data-level="<?php echo $key; ?>"><?php echo $value['label']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </li>
</script>

<script type="text/html" id="tmpl-ccgp-tab">
    <fieldset id="tabs-{{data.tab_id}}" class="tab-details half-block">
        <h4 class="tab-title">Tab {{data.tab_id}} details</h4>
        <a href="#" class="toggle-details-pane">Edit details</a>
        <div class="details-pane">
            <label for="ccgp-tab-{{data.tab_id}}-label" >Tab Label</label>
            <input type="text" id="ccgp-tab-{{data.tab_id}}-label" name="ccgp-tabs[{{data.tab_id}}][label]" value="{{data.details.label}}"/>
            <p class="info">This is the label as shown on the navigation tab</p>
            <label for="ccgp-tab-{{data.tab_id}}-slug" >Tab Slug (optional)</label>
            <input type="text" id="ccgp-tab-{{data.tab_id}}-slug" name="ccgp-tabs[{{data.tab_id}}][slug]" value="{{data.details.slug}}"/>
            <p class="info">The piece of the URL that follows your group&rsquo;s slug. E.g. http://www.communitycommons.org/groups/my-group/<strong>slug-to-use</strong></p>

            <label for="ccgp-tab-{{data.tab_id}}-visibility">Access</label>
            <select name="ccgp-tabs[{{data.tab_id}}][visibility]" id="ccgp-tab-{{data.tab_id}}-visibility" class="tab-visibility">
                <?php foreach ( $access_levels as $key => $value ) { ?>
                    <option value="<?php echo $value['bp_level'] ?>" data-level="<?php echo $key; ?>"><?php echo $value['label']; ?></option>
                <?php } ?>
            </select>

            <label><input type="checkbox" name="ccgp-tabs[{{data.tab_id}}][show-tab]" id="ccgp-tab-{{data.tab_id}}-show-tab" class="show-tab-setting" value="1" checked="checked"/> Include this tab in the hub navigation.</label>
            <p class="info">(Hiding the tab is non-standard behavior and should be avoided, unless you&rsquo;ve got another navigation method in place.)</p>

            <div id="navigation-order-{{data.tab_id}}" class="navigation-order-container">
                <label for="ccgp-tab-{{data.tab_id}}-nav-order" >Placement in Hub Navigation  (optional)</label>
                <input type="text" id="ccgp-tab-{{data.tab_id}}-nav-order" name="ccgp-tabs[{{data.tab_id}}][nav_order]" value="{{data.details.nav_order}}"/>
                <p class="info">Input a number (1-100) to change this tab&rsquo;s placement in the hub&rsquo;s navigation. Low numbers end up to the left by &ldquo;Home,&rdquo; high numbers end up near &ldquo;Manage.&rdquo;</p>
            </div>

            <a href="#" class="remove-tab">Remove this tab</a>
        </div>
        <div class="page-list">
            <h5>Pages in this section:</h5>
            <a href="#" class="ccgp-add-page button alignright">Add a new page</a>
            <p class="info">The first page is used as the section&rsquo;s landing page.</p>
            <ul id="section-{{data.tab_id}}" class="sortable no-bullets">
            </ul>
        </div>
    </fieldset>
</script>
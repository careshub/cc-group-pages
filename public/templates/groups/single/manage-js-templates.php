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
            <p>
                <label for="ccgp-page-{{data.post_id}}-visibility">Access</label>
                <select name="ccgp-pages[{{data.post_id}}][visibility]" id="ccgp-page-{{data.post_id}}-visibility">
                    <?php foreach ( $access_levels as $key => $value ) { ?>
                        <option value="<?php echo $value['bp_level'] ?>" data-level="<?php echo $key; ?>"><?php echo $value['label']; ?></option>
                    <?php } ?>
                </select>
            </p>
        </div>
    </li>
</script>

<script type="text/html" id="tmpl-ccgp-tab">
    <fieldset id="tabs-{{data.tab_id}}" class="tab-details half-block">
        <h4>Tab {{data.tab_id}} details</h4>
        <a href="#" class="toggle-details-pane alignright">Edit</a>
        <div class="details-pane">
            <label for="ccgp-tab-{{data.tab_id}}-label" >Tab Label</label>
            <input type="text" id="ccgp-tab-{{data.tab_id}}-label" name="ccgp-tabs[{{data.tab_id}}][label]" value="{{data.details.label}}"/>
            <p class="info">This is the label as shown on the navigation tab</p>
            <label for="ccgp-tab-{{data.tab_id}}-slug" >Tab Slug</label>
            <input type="text" id="ccgp-tab-{{data.tab_id}}-slug" name="ccgp-tabs[{{data.tab_id}}][slug]" value="{{data.details.slug}}"/>
            <p class="info">The piece of the URL that follows your group&rsquo;s slug. E.g. http://www.communitycommons.org/groups/my-group/<strong>slug-to-use</strong></p>
            <p>
                <label for="ccgp-tab-{{data.tab_id}}-visibility">Access</label>
                <select name="ccgp-tabs[{{data.tab_id}}][visibility]" id="ccgp-tab-{{data.tab_id}}-visibility">
                    <?php foreach ( $access_levels as $key => $value ) { ?>
                        <option value="<?php echo $value['bp_level'] ?>" data-level="<?php echo $key; ?>"><?php echo $value['label']; ?></option>
                    <?php } ?>
                </select>
            </p>
        </div>
        <h5>Pages in this section:</h5>
        <a href="#" class="ccgp-add-page alignright">Add a new page</a><p class="info">The first page is used as the landing page for this section.</p>
        <ul id="section-{{data.tab_id}}" class="sortable no-bullets">
        </ul>
    </fieldset>
</script>
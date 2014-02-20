var Obf = (function() {
    "use strict";

    var groupid = -1;
    var userid = -1;
    var options = {};

    var has_group_tabs = function() {
        return $j('.tabswrap').size() > 0;
    };

    var has_profile_subnav = function() {
        return $j('#sub-nav').size() > 0;
    };
    
    var has_supervisor_tabs = function () {
        return $j('#sub-nav').size() > 0;
    };

    var add_issuance_tab = function() {
        var url = window.config.wwwroot + 'interaction/obf/group.php?id=' + groupid;
        var link = $j('<a></a>').text(options.lang.badges).attr('href', url);
        var listelement = $j('<li></li>').append(link).addClass('badges');

        $j('ul.in-page-tabs').first().append(listelement);
    };

    var add_backpack_tab = function() {
        var url = window.config.wwwroot + 'interaction/obf/profile.php?user=' + userid;
        var link = $j('<a></a>').text(options.lang.backpacksettings).attr('href', url);
        var listelement = $j('<li></li>').append(link).addClass('backpack');

        $j('#sub-nav > ul').append(listelement);
    };
    
    var add_supervisor_tab = function() {
        var url = window.config.wwwroot + 'interaction/obf/supervisor.php';
        var link = $j('<a></a>').text(options.lang.badges).attr('href', url);
        var listelement = $j('<li></li>').append(link).addClass('badges');
        
        $j('#sub-nav > ul').append(listelement);
    };

    var toggle_category = function(evt) {
        $j(this).toggleClass('active');
        apply_category_change();
    };

    var reset_category_filter = function() {
        $j('ul.badge-categories button').removeClass('active');
        apply_category_change();
    };

    var apply_category_change = function() {
        var selected_buttons = $j('ul.badge-categories button.active');
        var selected_categories = selected_buttons.map(function() {
            return $j(this).text();
        });

        $j('ul#badges li').each(function(index) {
            var badge_categories = $j.parseJSON($j(this).attr('data-categories'));
            var show_badge = true;

            // Show all badges if none of the categories is selected.
            if (selected_categories.length === 0) {
                show_badge = true;
            }
            else {
                $j.each(selected_categories, function(index, category) {
                    if ($j.inArray(category, badge_categories) === -1) {
                        show_badge = false;
                    }
                });
            }

            $j(this)[show_badge ? 'fadeIn' : 'fadeOut']('fast');
        });
    };
    
    var create_issue_to_all_button = function () {
        var btn = $j('<button></button>').attr('type', 'button').text(get_string('issuetoall'));
        btn.click(function () {
            $j('select#users_potential option').attr('selected', true);
            users_moveopts('potential', 'members');
        });
        
        $j('td.lrbuttons').append(btn);
    };

    return {
        init_group: function(gid, opts) {
            options = opts;
            groupid = gid;

            if (has_group_tabs()) {
                add_issuance_tab();
            }
        }

        , init_profile: function(uid, opts) {
            options = opts;
            userid = uid;

            if (has_profile_subnav()) {
                add_backpack_tab();
            }
        }

        , init_categories: function() {
            $j('ul.badge-categories').on('click', 'button', toggle_category);
            $j('button.badge-reset-filter').click(reset_category_filter);
        }
        
        , init_supervisor: function (opts) {
            options = opts;
            
            if (has_supervisor_tabs()) {
                add_supervisor_tab();
            }
        }

        , init_issuance_page: function() {            
            create_issue_to_all_button();
            
            $j('#badge-issue-form-wrapper .tabswrap ul').on('click', 'li', function (evt) {
                evt.preventDefault();
                
                $j(this).siblings().removeClass('current-tab');
                $j(this).siblings().children('a').removeClass('current-tab');
                $j(this).addClass('current-tab');
                $j(this).children('a').addClass('current-tab');
                
                var tabname = $j(this).data('tab');
                var content = $j('#badge-issue-form-wrapper .subpage div[data-tab-content="' + tabname +'"]');
                
                content.siblings().hide();
                content.show();
            });
        }

        , connect_to_backpack: function(evt) {
            evt.preventDefault();

            navigator.id.get(function(assertion) {
                if (assertion !== null) {
                    $j.getJSON(window.config.wwwroot + 'interaction/obf/authenticate.json.php', {
                        assertion: assertion},
                    function(res, status, xhr) {
                        window.location.reload();
                    });
                }
            });
        }
    };
})();
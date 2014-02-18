var Obf = (function() {
    "use strict";

    var groupid = -1;
    var userid = -1;
    var options = {};
    var emailcache = {};

    var has_group_tabs = function() {
        return $j('.tabswrap').size() > 0;
    };

    var has_profile_subnav = function() {
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

//    var reset_expiration_date = function() {
//        var now = new Date();
//        $j('#expires_year').val(now.getFullYear());
//        $j('#expires_months').val(now.getMonth() + 1);
//        $j('#expires_days').val(now.getDay());
//        $j('#expires_optional').prop('checked', true).trigger('change');
//    };

//    var add_expiration_months = function(addedmonths) {
//        var years = parseInt($j('#expires_year').val());
//        var months = parseInt($j('#expires_month').val());
//        var days = parseInt($j('#expires_day').val());
//        var d = new Date(years, months - 1, days);
//
//        d.setMonth(d.getMonth() + parseInt(addedmonths));
//
//        $j('#expires_year').val(d.getFullYear());
//        $j('#expires_month').val(d.getMonth() + 1);
//        $j('#expires_days').val(d.getDay());
//    };

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

//    var show_badge_email = function(badgeid) {
//        if (!!emailcache[badgeid]) {
//            set_email_template(emailcache[badgeid]);
//            return;
//        }
//
//        $j.getJSON(window.config.wwwroot + 'interaction/obf/email.json.php', {
//            badgeid: badgeid,
//            group: groupid
//        }, function(res, status, xhr) {
//            emailcache[badgeid] = res.message;
//            set_email_template(emailcache[badgeid]);
//        });
//    };

//    var set_email_template = function(email) {
//        $j('#issuance_subject').val(email.subject);
//        $j('#issuance_body').val(email.body);
//        $j('#issuance_footer').val(email.footer);
//    };
    
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

//        , select_badge: function(badgeid) {
//            var selected = $j('ul#badges li[data-id=' + badgeid + ']');
//
//            if (!!selected) {
//                show_badge_email(badgeid);
//
//                var expiresat = selected.attr('data-expires');
//                reset_expiration_date();
//
//                if (expiresat != 0) {
//                    $j('#expires_optional').prop('checked', false).trigger('change');
//                    add_expiration_months(expiresat);
//                }
//
//                $j('#issuance_badge').val(badgeid);
//
//                $j(selected).siblings().hide();
//                $j('#badge-selector p.info').hide();
//                $j('.badge-category-wrapper').hide();
//
//                $j('#badge-selector p.show-badges').show();
//                $j('#user-selector').fadeIn();
//            }
//        }

//        , show_badge_selector: function(evt) {
//            evt.preventDefault();
//
//            $j('#user-selector').hide();
//            $j('ul#badges li').fadeIn();
//            $j('.badge-category-wrapper').fadeIn();
//            $j('#badge-selector p.show-badges').hide();
//        }

        , init_categories: function() {
            $j('ul.badge-categories').on('click', 'button', toggle_category);
            $j('button.badge-reset-filter').click(reset_category_filter);
        }

        , init_issuance_page: function() {            
            // Change the issued badge.
//            $j('#badge-selector p.show-badges a').click(Obf.show_badge_selector);

            // Badge selected, show recipient selector.
//            $j('ul#badges').on('click', 'li', function(evt) {
//                Obf.select_badge($j(this).attr('data-id'));
//            });
            
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
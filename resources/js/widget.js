(function($) {
    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    var NBSP = ' ';   // &nbsp;
    var THINSP = ' '; // &thinsp;

    if (typeof(hljs) === 'object') {
        hljs.registerLanguage('redis', function () {
            // I'm sorry for this hack
            return {
                c: [
                    {
                        cN: 'number',
                        b: '\\b\\d+(\\.\\d+)?',
                    },
                    {
                        cN: 'keyword',
                        b: '[A-Z]+\\b',
                    },
                    {
                        cN: 'deletion',
                        b: '[' + NBSP + THINSP + ']+',
                    },
                    {
                        cN: 'string',
                        b: '\\b[\\w\\-\\:\\_]+\\b',
                    },
                ]
            };
        });
    }

    /**
     * Widget for the displaying redis commands
     *
     * Options:
     *  - data
     */
    var PredisCommandsWidget = PhpDebugBar.Widgets.PredisCommandsWidget = PhpDebugBar.Widget.extend({

        className: csscls('predis'),

        render: function() {
            this.$status = $('<div />')
                .addClass(csscls('status'))
                .appendTo(this.$el);

            this.$list = new PhpDebugBar.Widgets.ListWidget({ itemRenderer: function(li, stmt) {
                var code = stmt.method;

                for (var i = 0; i < stmt.arguments.length; i++) {
                    var arg = stmt.arguments[i];

                    if (arg === '') {
                        code += ' '+ THINSP;
                    } else if (/^\s+$/.test(arg)) {
                        code += ' ' + arg.replace(/\s/g, NBSP);
                    } else {
                        code += ' ' + arg;
                    }
                }

                $('<code />')
                    .html(PhpDebugBar.Widgets.highlight(code, 'redis'))
                    .appendTo(li);

                if (typeof(stmt.connection) != 'undefined' && stmt.connection) {
                    $('<span title="Connection" />')
                        .addClass(csscls('connection'))
                        .text(stmt.connection)
                        .appendTo(li);
                }

                li
                    .data('method', stmt.method)
                    .data('arguments', stmt.arguments)
                    .data('connecton', stmt.connection);

                li.trigger('prediscommandswidgetitem');
            }});

            this.$list.$el.appendTo(this.$el);

            this.bindAttr('data', function(data) {
                // the Redis collector maybe is empty
                if (data.length <= 0) {
                    return false;
                }

                this.$list.set('data', data.profiles);
                this.$status.empty();

                // Search for duplicate and failed profiles.
                for (var map = {}, unique = 0, duplicate = 0, failed = 0, i = 0; i < data.profiles.length; i++) {
                    var stmt = data.profiles[i].method + data.profiles[i].arguments.join();
                    map[stmt] = map[stmt] || { keys: [] };
                    map[stmt].keys.push(i);
                }

                // Add classes to all duplicate profiles.
                for (var stmt in map) {
                    if (map[stmt].keys.length > 1) {
                        duplicate += map[stmt].keys.length;
                        for (var i = 0; i < map[stmt].keys.length; i++) {
                            this.$list.$el
                                .find('.' + csscls('list-item'))
                                .eq(map[stmt].keys[i])
                                .addClass(csscls('duplicate'))
                                .attr('title', 'Duplicate query')
                            ;
                        }
                    } else {
                        unique++;
                    }
                }

                var t = $('<span />')
                    .text(data.nb_profiles + " commands were executed")
                    .appendTo(this.$status);

                if (duplicate) {
                    t.append(", " + duplicate + " of which were duplicates");
                    t.append(", " + unique + " unique");
                }

                this.$el.trigger('prediscommandswidgetready');
            });
        }
    });
})(PhpDebugBar.$);

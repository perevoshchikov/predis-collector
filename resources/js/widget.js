(function($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    /**
     * Widget for the displaying redis queries
     *
     * Options:
     *  - data
     */
    var RedisQueriesWidget = PhpDebugBar.Widgets.RedisQueriesWidget = PhpDebugBar.Widget.extend({

        className: csscls('redis'),

        render: function() {
            this.$status = $('<div />')
                .addClass(csscls('status'))
                .appendTo(this.$el);

            this.$list = new PhpDebugBar.Widgets.ListWidget({ itemRenderer: function(li, stmt) {
                $('<code />')
                    .html(PhpDebugBar.Widgets.highlight(stmt.prepared_stmt, 'sql'))
                    .appendTo(li);

                if (stmt.duration_str) {
                    $('<span title="Duration" />')
                        .addClass(csscls('duration'))
                        .text(stmt.duration_str)
                        .appendTo(li);
                }

                if (stmt.memory_str) {
                    $('<span title="Memory usage" />')
                        .addClass(csscls('memory'))
                        .text(stmt.memory_str)
                        .appendTo(li);
                }

                if (typeof(stmt.connection_id) != 'undefined' && stmt.connection_id) {
                    $('<span title="Connection" />')
                        .addClass(csscls('connection'))
                        .text(stmt.connection_id)
                        .appendTo(li);
                }

                if (stmt.prepared_response) {
                    $('<span title="Response" />')
                        .addClass(csscls('response'))
                        .text(stmt.prepared_response)
                        .appendTo(li);
                }

                if (typeof(stmt.is_success) != 'undefined' && !stmt.is_success) {
                    li.addClass(csscls('error'));
                    li.append($('<span />')
                        .addClass(csscls('error'))
                        .text(stmt.error_message));
                }
            }});

            this.$list.$el.appendTo(this.$el);

            this.bindAttr('data', function(data) {
                // the Redis collector maybe is empty
                if (data.length <= 0) {
                    return false;
                }

                this.$list.set('data', data.statements);
                this.$status.empty();

                // Search for duplicate and failed statements.
                for (var map = {}, unique = 0, duplicate = 0, failed = 0, i = 0; i < data.statements.length; i++) {
                    var stmt = data.statements[i].prepared_stmt;
                    map[stmt] = map[stmt] || { keys: [] };
                    map[stmt].keys.push(i);

                    if (data.statements[i].is_success === false) {
                        failed++;
                    }
                }

                // Add classes to all duplicate statements.
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
                    .text(data.nb_statements + " statements were executed")
                    .appendTo(this.$status);

                if (failed) {
                    t.append(", " + failed + " of which failed");
                }

                if (duplicate) {
                    t.append(", " + duplicate + " of which were duplicates");
                    t.append(", " + unique + " unique");
                }

                if (data.duration_str) {
                    this.$status
                        .append(
                            $('<span title="Accumulated duration" />')
                                .addClass(csscls('duration'))
                                .text(data.duration_str)
                        );
                }

                if (data.memory_str) {
                    this.$status
                        .append(
                            $('<span title="Memory usage" />')
                                .addClass(csscls('memory'))
                                .text(data.memory_str)
                        );
                }
            });
        }
    });
})(PhpDebugBar.$);
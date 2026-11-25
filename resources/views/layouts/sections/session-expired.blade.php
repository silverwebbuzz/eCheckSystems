{{--
    Session-expired handling for every page using commonMaster (admin + customer).
    When a background request fails with 401 (login expired, see App\Http\Middleware\AjaxSessionExpired)
    or 419 (security token expired), show one clear message and open the login page,
    instead of DataTables' "Invalid JSON response" popup. Other errors behave as before.
    This app loads two jQuery copies (CDN in <head> + bundled scripts), so register on both.
--}}
<script>
    (function () {
        var handled = false;

        function isExpired(xhr) {
            return xhr && (xhr.status === 401 || xhr.status === 419);
        }

        function goToLogin(xhr) {
            if (handled) return;
            handled = true;
            var data = (xhr && xhr.responseJSON) || {};
            alert(data.message || 'Your session has expired. Please log in again.');
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload(); // the page itself redirects to the right login
            }
        }

        function register(jq) {
            if (!jq) return;

            if (!jq.__sessionExpiredAjax) {
                jq.__sessionExpiredAjax = true;
                jq(document).ajaxError(function (event, xhr) {
                    if (isExpired(xhr)) goToLogin(xhr);
                });
            }

            // DataTables may load after the first call, so patch it separately (once).
            if (jq.fn && jq.fn.dataTable && jq.fn.dataTable.ext && !jq.fn.dataTable.ext.__sessionExpiredPatched) {
                jq.fn.dataTable.ext.__sessionExpiredPatched = true;
                var previous = jq.fn.dataTable.ext.errMode;
                jq.fn.dataTable.ext.errMode = function (settings, techNote, message) {
                    if (settings && isExpired(settings.jqXHR)) {
                        goToLogin(settings.jqXHR); // ajaxError may not fire on this jQuery copy
                        return;
                    }
                    if (typeof previous === 'function') {
                        return previous.apply(this, arguments);
                    }
                    if (previous === 'throw') {
                        throw new Error(message);
                    }
                    if (previous !== 'none') {
                        alert(message);
                    }
                };
            }
        }

        register(window.jQuery);
        document.addEventListener('DOMContentLoaded', function () {
            register(window.jQuery); // bundled jQuery / DataTables are ready by now
        });
    })();
</script>

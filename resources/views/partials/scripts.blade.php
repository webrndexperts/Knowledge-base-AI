<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script type="text/javascript" src="{{ url('js/jquery/dist/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ url('js/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>

<!-- Adding helper scripts. -->
<script type="text/javascript" src="{{ url('js/helper.js?v='.time()) }}"></script>

@fluxScripts

<script>
    if (!document._notifyUserListenerAdded) {
        document.addEventListener('user-notify', event => {
            let { message = '', type = 'success' } = event.detail;
            toast(message, type);
            console.log(event.detail);
        });
        
        document._notifyUserListenerAdded = true;
    }
</script>
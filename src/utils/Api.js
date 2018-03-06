class Dokan_API  {

    endpoint() {
        return window.wpApiSettings.root + 'dokan/v1';
    }

    headers() {
        return {
        }
    }

    get(path, data = {} ) {
        return this.ajax(path, 'GET', this.headers(), data);
    }

    post(path, data = {} ) {
        return this.ajax(path, 'POST', this.headers(), data);
    }

    put(path, data = {} ) {
        data._method = 'PUT';
        return this.post(path, data);
    }

    delete(path, data = {} ) {
        return this.ajax(path, 'DELETE', this.headers(), data);
    }

    // jQuery ajax wrapper
    ajax(path, method, headers, data) {

        return $.ajax({
            url: this.endpoint() + path,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', window.wpApiSettings.nonce );
            },
            type: method,
            data: data
        });
    }
}

export default Dokan_API;

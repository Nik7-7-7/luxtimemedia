class MasterstudyApiProvider {
    constructor( routePrefix = '' ) {
        this.baseURL = `${api_data.rest_url}${routePrefix}`;
        this.nonce = api_data.nonce;
    }

    async get(route, params = {}, additionalHeaders = {}) {
        const url = this.getRouteUrl(route);

        const allParams = { date_from: getDateFrom(), date_to: getDateTo(), ...params };
        Object.keys(allParams).forEach(key => url.searchParams.append(key, allParams[key]));

        const headers = {
            'Content-Type': 'application/json',
            'X-WP-NONCE': this.nonce,
            ...additionalHeaders
        };

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: headers
            });

            if (!response.ok) {
                const errorData = await response.json();
                let errorMessage = `Status: ${response.status}, Error Code: ${errorData.error_code}`;

                if (errorData.message) {
                    errorMessage += `, Message: ${errorData.message}`;
                }

                if (errorData.errors) {
                    for (const [field, messages] of Object.entries(errorData.errors)) {
                        errorMessage += `, ${field}: ${messages.join(', ')}`;
                    }
                }

                throw new Error(errorMessage);
            }

            return response.json();
        } catch (error) {
            throw error;
        }
    }

    getRouteUrl(route) {
        return new URL(`${this.baseURL}${route}`);
    }

    getRouteNonce() {
        return this.nonce;
    }
}
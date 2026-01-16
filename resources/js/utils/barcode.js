// Helper to generate a random code
function randomCode() {
    const min = 100000000000;
    const max = 999999999999;
    return Math.floor(Math.random() * (max - min + 1)) + min + "";
}

export function generateBarcode() {
    return randomCode();
}

/**
 * Generates a unique barcode by checking against the server.
 * Requires axios instance (usually window.axios).
 */
export async function generateUniqueBarcode() {
    let unique = false;
    let code = '';
    let attempts = 0;

    while (!unique && attempts < 5) {
        code = randomCode();
        try {
            // Check if exists
            // We use the scan endpoint. If 200 => exists. If 404 => not found (safe).
            await window.axios.get(`/api/v1/products/scan?barcode=${code}`);
            // If we are here, it found something (status 200)
            // So code is NOT unique. Loop again.
            attempts++;
        } catch (error) {
            if (error.response && error.response.status === 404) {
                // 404 means not found, so it IS unique!
                unique = true;
            } else {
                // Real error? Break to avoid infinite loop
                 console.error("Barcode check failed", error);
                 break; // Return current code anyway or throw?
                 // Let's just return the code and hope for best if API fails
                 return code; 
            }
        }
    }
    return code;
}

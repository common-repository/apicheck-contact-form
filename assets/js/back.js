document.addEventListener('DOMContentLoaded', function() {
    // Function to toggle the visibility of the country selection dropdown
    function toggleCountrySelection() {
        const countrySelection = document.getElementById('country-selection');
        const enableAllCountries = document.getElementById('enable-all-countries');

        countrySelection.style.display = enableAllCountries.checked ? 'none' : 'block';
    }

    // Attach an event listener to the checkbox to trigger the toggle function
    const enableAllCountries = document.getElementById('enable-all-countries');
    if (enableAllCountries) { // Check if the checkbox exists on the page
        enableAllCountries.addEventListener('change', toggleCountrySelection);

        // Initial call to set the initial state
        toggleCountrySelection();
    }
});

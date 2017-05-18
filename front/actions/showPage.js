export default function showPage(payload) {
    return function(dispatch) {
        // DO NOT POPULATE
        // temporary solutition until flight list is not react components
        $(document).trigger(payload, [$('#flightsContainer')]);
    }
};

export default function showFlightsList(payload) {
    return function(dispatch) {
        // DO NOT POPULATE
        // temporary solutition until flight list is not react components
        $(document).trigger("flightListShow", [$('#flightsContainer')]);
    }
};

export default function trigger(payload, args) {
    args = Array.isArray(args) ? args : [];
    return function(dispatch) {
        // DO NOT POPULATE
        // temporary solutition until flight list is not react components
        $(document).trigger(payload, args);
    }
};

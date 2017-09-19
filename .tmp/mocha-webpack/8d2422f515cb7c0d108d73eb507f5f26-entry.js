
    var testsContext = require.context("../../front", false);

    var runnable = testsContext.keys();

    runnable.forEach(testsContext);
    
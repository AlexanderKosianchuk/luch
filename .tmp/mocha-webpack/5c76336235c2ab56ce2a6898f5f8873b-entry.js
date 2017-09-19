
    var testsContext = require.context("../../front/tests", false);

    var runnable = testsContext.keys();

    runnable.forEach(testsContext);
    
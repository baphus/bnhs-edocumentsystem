/**
 * Artillery processor for custom functions
 */

module.exports = {
  setCSRFToken,
  logResponse,
  generateRandomData
};

function setCSRFToken(requestParams, context, ee, next) {
  // Extract CSRF token from cookies or headers if needed
  if (context.vars.csrfToken) {
    requestParams.headers = requestParams.headers || {};
    requestParams.headers['X-CSRF-TOKEN'] = context.vars.csrfToken;
  }
  return next();
}

function logResponse(requestParams, response, context, ee, next) {
  if (response.statusCode >= 400) {
    console.log(`Error: ${response.statusCode} - ${requestParams.url}`);
  }
  return next();
}

function generateRandomData(context, events, done) {
  // Generate random test data
  context.vars.randomEmail = `test${Math.floor(Math.random() * 10000)}@example.com`;
  context.vars.randomName = `Test User ${Math.floor(Math.random() * 1000)}`;
  return done();
}

const functions = require("firebase-functions");
const admin = require('firebase-admin');
admin.initializeApp();


exports.aircrafts = functions.https.onRequest(async (request, response) => {

  const query = request.query;
  let ref = admin.firestore().collection('aircrafts');

  if (query.cost_above) {
    ref = ref.where("cost", ">=", +query.cost_above);
  }
  if (query.cost_below) {
    ref = ref.where("cost", "<=", +query.cost_below);
  }
  if (query.manufacturers) {
    const m = JSON.parse(query.manufacturers);
    if (m && m.length > 0) {
      ref = ref.where("manufacturer", "in", m);
    }
  }
  if (query.types) {
    const t = JSON.parse(query.types);
    if (t && t.length > 0) {
      ref = ref.where("type", "in", t);
    }
  }
  if (query.engineCount) {
    const e = JSON.parse(query.engineCount);
    if (e && e.length > 0) {
      ref = ref.where("engineCount", "in", e);
    }
  }
  if (query.status) {
    const s = JSON.parse(query.status);
    if (s && s.length > 0) {
      ref = ref.where("status", "in", s);
    }
  }


  ref = ref.limit(100);

  response.contentType('application/json');
  response.send((await ref.get()).docs.map(r => r.data()));
});

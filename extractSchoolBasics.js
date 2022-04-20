const fs = require("fs");

function jsonReader(filePath, cb) {
  fs.readFile(filePath, (err, fileData) => {
    if (err) {
      return cb && cb(err);
    }
    try {
      const object = JSON.parse(fileData);
      return cb && cb(null, object);
    } catch (err) {
      return cb && cb(err);
    }
  });
}
jsonReader("./berlin.json", (err, data) => {
  if (err) {
    console.log("Error reading file:", err);
    return;
  }

  console.log('');
  console.log(data.length + ' schools');

  data.sort(function(a,b) {
    if (a.name === b.name) {
        return a.id < b.id ? -1 : 1;
    }

    return a.name < b.name ? -1 : 1;
  });

  let csv = '';
  csv += ['bsn','id','name','address','zip','city','type','legal'].join(';') + "\n";

  data.forEach(school => {
    csv += [
        '',
        school.id,
        school.name,
        school.address,
        school.zip,
        school.city,
        school.schooltype,
        school.legal_status,
    ].join(';') + "\n";
  });

  console.log(csv.length + ' bytes basic school data');
  console.log('');

  fs.writeFile("./berlin-basics.csv", csv, err => {
    if (err) console.log("Error writing file:", err);
  });
});

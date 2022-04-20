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
  csv += ['bsn','id','name',
    'teacher2010','prospective2010',
    'teacher2011','prospective2011',
    'teacher2012','prospective2012',
    'teacher2013','prospective2013',
    'teacher2014','prospective2014',
    'teacher2015','prospective2015',
    'teacher2016','prospective2016',
    'teacher2017','prospective2017',
    'teacher2018','prospective2018',
    'teacher2019','prospective2019',
    'teacher2020','prospective2020',
    'teacher2021','prospective2021',
  ].join(';') + "\n";

  data.forEach(school => {
    if (school.staff) {
      const staffObj = [];
      school.staff.forEach(staff => {
        const year = staff.year.slice(-7).slice(0, 4);
        const key = staff.Bezeichnung === 'Lehrkräfte' ? 'teacher' : staff.Bezeichnung === 'Lehramtsanwärter/in' ? 'prospective' : 'unknown';
        const value = staff.Insgesamt.trim();
        staffObj[key + year] = value;
      });
      csv += [
          '',
          school.id,
          school.name,
          staffObj.teacher2010 || 0, staffObj.prospective2010 || 0,
          staffObj.teacher2011 || 0, staffObj.prospective2011 || 0,
          staffObj.teacher2012 || 0, staffObj.prospective2012 || 0,
          staffObj.teacher2013 || 0, staffObj.prospective2013 || 0,
          staffObj.teacher2014 || 0, staffObj.prospective2014 || 0,
          staffObj.teacher2015 || 0, staffObj.prospective2015 || 0,
          staffObj.teacher2016 || 0, staffObj.prospective2016 || 0,
          staffObj.teacher2017 || 0, staffObj.prospective2017 || 0,
          staffObj.teacher2018 || 0, staffObj.prospective2018 || 0,
          staffObj.teacher2019 || 0, staffObj.prospective2019 || 0,
          staffObj.teacher2020 || 0, staffObj.prospective2020 || 0,
          staffObj.teacher2021 || 0, staffObj.prospective2021 || 0,
      ].join(';') + "\n";
    }
  });

  console.log(csv.length + ' bytes staff school data');
  console.log('');

  fs.writeFile("./berlin-staff.csv", csv, err => {
    if (err) console.log("Error writing file:", err);
  });
});

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
    if (school.students) {
      const studentObj = [];
      school.students.forEach(student => {
        const year = parseInt(student.year.slice(0, 4), 10);
        let key = student.Jahrgangsstufe;
        const value = student.Insgesamt.trim();

        if (year !== 2020) {
          return;
        }

        if (key === 'Jahrgangsstufe 01') {
          key = 'grade1';
        } else if (key === 'Jahrgangsstufe 02') {
          key = 'grade2';
        } else if (key === 'Jahrgangsstufe 03') {
          key = 'grade3';
        } else if (key === 'Jahrgangsstufe 03 (incl. JüL 1-3)') {
          key = 'grade3_interyear123';
        } else if (key === 'Jahrgangsstufe 04') {
          key = 'grade4';
        } else if (key === 'Jahrgangsstufe 05') {
          key = 'grade5';
        } else if (key === 'Jahrgangsstufe 06') {
          key = 'grade6';
        } else if (key === 'Jahrgangsstufe 06 (incl. JüL 4-6)') {
          key = 'grade6_interyear456';
        } else if (key === 'Jahrgangsstufe 07') {
          key = 'grade7';
        } else if (key === 'Jahrgangsstufe 08') {
          key = 'grade8';
        } else if (key === 'Jahrgangsstufe 09') {
          key = 'grade9';
        } else if (key === 'Jahrgangsstufe 10') {
          key = 'grade10';
        } else if (key === 'Jahrgangsstufe 11') {
          key = 'grade11';
        } else if (key === 'Jahrgangsstufe 12') {
          key = 'grade12';
        } else if (key === 'Jahrgangsstufe 13') {
          key = 'grade13';
        } else if (key === 'E-Phase') {
          key = 'gradeE';
        } else if (key === '1. Kurshalbjahr') {
          key = 'gradeQ1';
        } else if (key === '2. Kurshalbjahr') {
          key = 'gradeQ2';
        } else if (key === '3. Kurshalbjahr') {
          key = 'gradeQ3';
        } else if (-1 !== ['Eingangsstufe', 'Unterstufe', 'Mittelstufe', 'Oberstufe', 'Abschlussstufe',
          'Berufliche Schulen mit sonderpäd. Aufgab  Lehrgänge',
          'Berufliche Schulen mit sonderpäd. Aufgab  Auszubildende',
          'Berufsfachschulen  mehrjährig',
        ].indexOf(key)) {
          key = 'gradeOther';
        } else {
          console.log(year, key, value);
          throw 'unknown';
        }
        studentObj[key] = value;
      });
      csv += [
          '',
          school.id,
          school.name,
/*          staffObj.teacher2010 || 0, staffObj.prospective2010 || 0,
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
          staffObj.teacher2021 || 0, staffObj.prospective2021 || 0,*/
      ].join(';') + "\n";
    }
  });

  console.log(csv.length + ' bytes students school data');
  console.log('');

  fs.writeFile("./berlin-students.csv", csv, err => {
    if (err) console.log("Error writing file:", err);
  });
});

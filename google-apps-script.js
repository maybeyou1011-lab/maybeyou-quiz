// ═══ 貼到 Google Apps Script 裡面 ═══

function doPost(e) {
  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    var data = JSON.parse(e.postData.contents);

    var labelMap = {
      gender:   { male: '男性', female: '女性' },
      age:      { teen: '18歲以下', young: '19-35歲', middle: '36-55歲', senior: '55歲以上' },
      goal:     { slim: '瘦身', digest: '消化', energy: '精神', sleep: '睡眠', beauty: '美容', immunity: '免疫' },
      diet:     { eating_out: '外食', carbs: '澱粉控', balanced: '均衡', fitness: '健身' },
      gut:      { bloat: '脹氣', constipation: '便秘', sensitive: '敏感', normal: '正常' },
      sleep:    { poor: '品質差', stressed: '壓力大', tired: '易疲倦', good: '正常' },
      skin:     { dull: '暗沉', unstable: '不穩定', intimate: '私密', none: '無' },
      exercise: { none: '不運動', light: '偶爾', regular: '規律', intense: '高強度' }
    };

    function t(cat, val) {
      return (labelMap[cat] && labelMap[cat][val]) || val;
    }

    sheet.appendRow([
      new Date().toLocaleString('zh-TW', { timeZone: 'Asia/Taipei' }),
      t('gender', data.gender),
      t('age', data.age),
      t('goal', data.goal),
      t('diet', data.diet),
      t('gut', data.gut),
      t('sleep', data.sleep),
      t('skin', data.skin),
      t('exercise', data.exercise),
      data.result_code,
      data.result_name
    ]);

    return ContentService
      .createTextOutput(JSON.stringify({ success: true }))
      .setMimeType(ContentService.MimeType.JSON);

  } catch (err) {
    return ContentService
      .createTextOutput(JSON.stringify({ success: false, error: err.toString() }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

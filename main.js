(function($) {
  var allVoices, bootstrap, apiKey, selectedLang, selectedVoice, testAudio = document.createElement("AUDIO");
  $(onDomReady);
  
  function onDomReady() {
    apiKey = $("#sitespeaker_key").val() || "";
    selectedLang = $("#sitespeaker_lang").data("value") || "";
    selectedVoice = $("#sitespeaker_voice").data("value") || "";
    Promise.all([loadVoices(), loadBootstrap()]).then(update);
    $("#sitespeaker_key").change(function() {
      apiKey = this.value;
      loadVoices().then(update);
    })
    $("#sitespeaker_lang").change(function() {
      selectedLang = this.value;
      update();
    })
    $("#sitespeaker_voice").change(function() {
      selectedVoice = this.value;
      update();
    })
    $("#sitespeaker_test").click(function() {
      if (selectedLang && selectedVoice) {
        $.get("https://ws.readaloudwidget.com/test-voice?lang=" + selectedLang + "&voice=" + encodeURIComponent(selectedVoice), function(res) {
          testAudio.src = res.url;
          testAudio.play();
        })
      }
    })
  }

  function loadVoices() {
    if (!apiKey) {
      allVoices = [];
      return Promise.resolve();
    }
    return new Promise(function(fulfill) {
      $.post({
        url: "https://ws.readaloudwidget.com/list-voices",
        data: JSON.stringify({key: apiKey}),
        contentType: "application/json",
        dataType: "json",
        success: function(res) {
          allVoices = res;
          fulfill();
        },
        error: function() {
          alert("Failed to load voice list, possibly invalid API key");
          allVoices = [];
          fulfill();
        }
      })
    })
  }

  function loadBootstrap() {
    return new Promise(function(fulfill) {
      $.get("https://assets.readaloudwidget.com/embed/code.html", function(res) {
        bootstrap = res;
        fulfill();
      })
    })
  }

  function update() {
    var languages = Array.from(new Set(allVoices.map(function(voice) {return voice.lang}))).sort();
    if (selectedLang && languages.indexOf(selectedLang) == -1) selectedLang = "";
    $("#sitespeaker_lang").empty();
    $("<option>").val("").appendTo("#sitespeaker_lang");
    languages.forEach(function(lang) {
      $("<option>").val(lang)
        .text(lang)
        .appendTo("#sitespeaker_lang");
    })
    $("#sitespeaker_lang").val(selectedLang);

    var voices = allVoices.filter(function(voice) {return voice.lang == selectedLang}).sort(function(a,b) {return a.desc.localeCompare(b.desc)});
    if (selectedVoice && selectedVoice != "free" && !voices.some(function(voice) {return voice.name == selectedVoice})) selectedVoice = "";
    $("#sitespeaker_voice").empty();
    $("<option>").val("").appendTo("#sitespeaker_voice");
    $("<option>").val("free")
      .text("[free] Use browser-provided TTS voices")
      .appendTo("#sitespeaker_voice");
    voices.forEach(function(voice) {
      $("<option>").val(voice.name)
        .text((voice.desc||voice.name) + " (" + voice.gender[0].toLowerCase() + ")")
        .appendTo("#sitespeaker_voice");
    })
    $("#sitespeaker_voice").val(selectedVoice);
    $("#sitespeaker_test").toggle(!!selectedVoice && selectedVoice != "free");

    var apiKey = $("#sitespeaker_key").val();
    if (apiKey && selectedLang && selectedVoice) {
      var code = bootstrap
        .replace("${key}", apiKey)
        .replace("${lang}", selectedLang)
        .replace("${voice}", selectedVoice);
      $("#sitespeaker_code").val(code);
    }
    else {
      $("#sitespeaker_code").val("");
    }
  }
})
(jQuery)

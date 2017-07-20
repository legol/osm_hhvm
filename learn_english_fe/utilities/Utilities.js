class Utilities {
  get(_url, _onSuccess, onError) {
    console.log('http get: ' + _url);
    $.ajax({
      url: _url,
      crossDomain: true,
      dataType: 'json',
      async: true,
      type: 'GET',
      timeout: 30000, // milliseconds
      context:this,
    })
      .done(function(data, textStatus, jqXHR) {
        console.log('request succeeded: ' + JSON.stringify(data));

        if (_onSuccess) {
          _onSuccess(data);
        }
      })
      .fail(function(jqXHR, textStatus, errorThrown) {
        if (_onError) {
          _onError(errorThrown);
        }
      })
      .always(function() {
      });
  }

  post(_url, _data, _onSuccess, _onError){
    console.log('http post: ' + _url + '\n data: ' + _data);

    $.ajax({
      url : _url,
      data : _data,
      crossDomain: true,
      dataType: 'json', // incoming data type
      async: true,
      type: "POST",
      timeout: 30000, // milliseconds
      context: this,
    })
      .done(function(data, textStatus, jqXHR) {
        console.log('request succeeded: ' + JSON.stringify(data));

        if (_onSuccess) {
          _onSuccess(data);
        }
      })
      .fail(function(jqXHR, textStatus, errorThrown) {
        if (_onError) {
          _onError(errorThrown);
        }
      })
      .always(function() {
      });
  }
}

export { Utilities };

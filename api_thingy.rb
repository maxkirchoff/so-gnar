#!/usr/bin/ruby
require 'open-uri'

class API_THINGY

  def initialize(credentials)
    # Instance variables
    @credentials = credentials
  end

  def get_status(endpoint)
    begin
      open('http://'+endpoint, :http_basic_authentication=>[@credentials['username'], @credentials['password']]) do |response|
        return response.status[0]
      end
    rescue OpenURI::HTTPError => the_error
      return the_error.io.status[0]
    end
  end
end
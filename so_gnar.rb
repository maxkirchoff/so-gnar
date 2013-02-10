#!/usr/bin/ruby
require 'YAML'
# api_thingy.rb
require File.expand_path(".",Dir.pwd) + '/api_thingy.rb'

def get_config
  # load our yml conf
  YAML.load_file('conf/conf.yml')
end

def status_alerts(config)
  alerts = Hash.new
  config['service_clusters'].each do |key, service_cluster|
    api = API_THINGY.new(service_cluster['credentials'])
    service_cluster['endpoints'].each do |endpoint|
      response = api.get_status(endpoint['url'])
      case response.to_i
      when 200
        # do nothing
      when 400
        alerts[endpoint['url']] = endpoint['name'] + ' thinks you are making a bad request.'
      when 401
        alerts[endpoint['url']] = endpoint['name'] + ' thinks you have bad credentials.'
      when 403
        alerts[endpoint['url']] = endpoint['name'] + ' thinks this is forbidden.'
      when 404
        alerts[endpoint['url']] = endpoint['name'] + ' is not found.'
      when 500
        alerts[endpoint['url']] = endpoint['name'] + ' is broken.'
      else
        alerts[endpoint['url']] = endpoint['name'] + ' is returning an Unknown status code.'
      end
    end
  end
  return alerts
end

def audio_success(last_run)
  config = get_config
  system('afplay "' + config['alert_success'] + '"')
  if last_run[:failure] > last_run[:success]
    system('say "' + config['alert_restored'] + '"')
  end
end

def audio_failure(status_alerts)
  config = get_config
  system('afplay "' + config['alert_failure'] + '"')
  status_alerts.values.each do |status_alert|
    system('say "' + status_alert + '"')
  end
end

last_run = {
    # Set our last run success timestamp for 0 so it announces any success results immediately.
    :success => 0,
    # Set our last run failure timestamp for 0 so it announces any failure results immediately.
    :failure => 0
}

while true
  config = get_config

  if config['service_clusters'] && config['service_clusters'].is_a?(Object)
    status_alerts = status_alerts(config)

    if config['alerts'].any?
      if status_alerts.empty?
        success_threshold = ((Time.now.to_i - config['alert_frequency']) > last_run[:success])
        if success_threshold || (last_run[:failure] > last_run[:success])
          if config['alerts'].include?('audio')
            audio_success(last_run)
          end
          last_run[:success] = Time.now.to_i
        end
      else
        failure_threshold = ((Time.now.to_i - config['alert_frequency']) > last_run[:failure])
        if failure_threshold || (last_run[:failure] < last_run[:success])
          if config['alerts'].include?('audio')
            audio_failure(status_alerts)
          end
          last_run[:failure] = Time.now.to_i
        end
      end
    end
  end
  sleep config['status_check_frequency']
end
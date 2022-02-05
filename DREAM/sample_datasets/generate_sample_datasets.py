import csv
import datetime
from datetime import date
import numpy as np
from random import randint, uniform

# date
today = date.today()

# city
cities = ['Hyderabad', 'Warangal', 'Nizamabad', 'Khammam', 'Karimnagar', 'Ramagundam', 'Mahabubnagar', 'Adilabad', 'Suryapet', 'Siddipet', 'Nalgonda', 'Jagtial']

# weather (repeat sunny and rainy for being more likely to appear)
weather = ['sunny']*10
weather.extend(['partially cloudy']*5)
weather.extend(['cloudy']*2)
weather.extend(['foggy', 'stormy', 'tornado', 'hurricane'])
weather.extend(['rainy']*7)

# windDirection
wind_directions = ['n', 's', 'e', 'o', 'ne', 'se', 'no', 'so']

# crop
crops = ['potatoes', 'tomatoes', 'salad', 'onions', 'radishes', 'cucumber', 'cauliflower']

# fertilizers
fertilizers = ['Ammonium chloride', 'Ammonium sulphate', 'CAN', 'DAP', 'NP / NPK complexes', 'SSP', 'Urea']

################ create weather reports
n_reports_for_city = 400

with open('src/Command/sample_datasets/weather_reports.csv', mode='w+') as f:
    writer = csv.writer(f, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
    # the first row is the header
    writer.writerow(['date', 'city', 'weather', 't_max', 't_min', 't_avg', 'rain_mm', 'windSpeed', 'windDirection', 'humidity', 'pressure'])

    # write body of csv
    for city in cities:
      # indexes
      weather_indexes = np.random.choice(len(weather), n_reports_for_city)
      directions_indexes = np.random.choice(len(wind_directions), n_reports_for_city)
      # write rows
      for report in range(n_reports_for_city):
        t_max = randint(10, 30)
        t_min = randint(0, t_max - 8)
        t_avg = randint(t_min + 3, t_max - 3)
        ww = weather[weather_indexes[report]]
        if ww == 'rainy' or ww == 'stormy' or ww == 'hurricane':
          rain_mm = randint(5, 200)
        else:
          rain_mm = 0
        writer.writerow([today - datetime.timedelta(days=report+1), city, ww, t_max, t_min, t_avg, rain_mm, round(uniform(0.5, 15),1), wind_directions[directions_indexes[report]], randint(10,70), randint(950, 1200)])


################# create weather forecasts
days_forecasts = 6

with open('src/Command/sample_datasets/weather_forecasts.csv', mode='w+') as f:
    writer = csv.writer(f, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
    # the first row is the header
    writer.writerow(['date', 'city', 'weather', 't_max', 't_min', 't_avg', 'rain_mm', 'windSpeed', 'windDirection', 'humidity', 'pressure'])

    # write body of csv
    for city in cities:
      # indexes
      weather_indexes = np.random.choice(len(weather), days_forecasts)
      directions_indexes = np.random.choice(len(wind_directions), days_forecasts)
      # write rows
      for forecast in range(days_forecasts):
        t_max = randint(10, 30)
        t_min = randint(0, t_max - 8)
        t_avg = randint(t_min + 3, t_max - 3)
        ww = weather[weather_indexes[forecast]]
        if ww == 'rainy' or ww == 'stormy' or ww == 'hurricane':
          rain_mm = randint(5, 200)
        else:
          rain_mm = 0
        writer.writerow([today + datetime.timedelta(days=forecast+1), city, ww, t_max, t_min, t_avg, rain_mm, round(uniform(0.5, 15),1), wind_directions[directions_indexes[forecast]], randint(10,70), randint(950, 1200)])
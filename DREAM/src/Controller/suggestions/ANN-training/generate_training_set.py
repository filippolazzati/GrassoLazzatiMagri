import csv
import datetime
from datetime import date
import numpy as np
from random import randint, uniform

# define some utility functions to convert labels to numbers and vice versa
def direction_to_number(label):
  # ['n', 's', 'e', 'o', 'ne', 'se', 'no', 'so']
  number = -1
  if label == 'n':
    number = 0
  if label == 's':
    number = 1
  if label == 'e':
    number = 2
  if label == 'o':
    number = 3
  if label == 'ne':
    number = 4
  if label == 'se':
    number = 5
  if label == 'no':
    number = 6
  if label == 'so':
    number = 7
  return number

def weather_to_number(label):
  number = -1
  if label == 'sunny':
    number = 0
  if label == 'partially cloudy':
    number = 1
  if label == 'cloudy':
    number = 2
  if label == 'rainy':
    number = 3
  if label == 'foggy':
    number = 4
  if label == 'stormy':
    number = 5
  if label == 'tornado':
    number = 6
  if label == 'hurricane':
    number = 7
  return number

def crop_to_number(label):
  # ['potatoes', 'tomatoes', 'salad', 'onions', 'radishes', 'cucumber', 'cauliflower']
  number = -1
  if label == 'potatoes':
    number = 0
  if label == 'tomatoes':
    number = 1
  if label == 'salad':
    number = 2
  if label == 'onions':
    number = 3
  if label == 'radishes':
    number = 4
  if label == 'cucumber':
    number = 5
  if label == 'cauliflower':
    number = 6
  return number

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

######################## create dataset for TRAINING fertilizers (last column is 'fertilizer')
n_samples = 50
n_weather_reports = 24
# the date does not matter since reports are taken from the current date on every 15 days
g = open('dataset_numerical_fertilizers.csv', 'w')
writer_g = csv.writer(g, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
with open('dataset_fertilizers.csv', mode='w') as f:
    writer = csv.writer(f, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
    # production data
    header = ['crop']
    # n_weather_reports (last year) of that city
    for i in range(n_weather_reports):
      header.extend(['weather'+str(i), 't_max'+str(i), 't_min'+str(i), 't_avg'+str(i), 'rain_mm'+str(i), 'windSpeed'+str(i), 'windDirection'+str(i), 'humidity'+str(i), 'pressure'+str(i)])
    header.append('fertilizer')
    # the first row is the header
    writer.writerow(header)
    writer_g.writerow(header)

    # write body of csv
    for sample in range(n_samples):
      crop = np.random.choice(len(crops), 1)
      sample_data = [crops[crop[0]]]
      sample_data_g = [crop_to_number(crops[crop[0]])]
      # indexes
      weather_indexes = np.random.choice(len(weather), n_weather_reports)
      directions_indexes = np.random.choice(len(wind_directions), n_weather_reports)
      for report in range(n_weather_reports):
        t_max = randint(10, 30)
        t_min = randint(0, t_max - 8)
        t_avg = randint(t_min + 3, t_max - 3)
        ww = weather[weather_indexes[report]]
        if ww == 'rainy' or ww == 'stormy' or ww == 'hurricane':
          rain_mm = randint(5, 200)
        else:
          rain_mm = 0
        sample_data.extend([ww, t_max, t_min, t_avg, rain_mm, round(uniform(0.5, 15),1), wind_directions[directions_indexes[report]], randint(10,70), randint(950, 1200)])
        sample_data_g.extend([weather_to_number(ww), t_max, t_min, t_avg, rain_mm, round(uniform(0.5, 15),1), direction_to_number(wind_directions[directions_indexes[report]]), randint(10,70), randint(950, 1200)])

      fertilizer = np.random.choice(len(fertilizers), 1)
      sample_data.append(fertilizers[fertilizer[0]])
      sample_data_g.append(fertilizers[fertilizer[0]])
      writer.writerow(sample_data)
      writer_g.writerow(sample_data_g)
g.close()

######################## create dataset for TRAINING crops (last column is 'crop')
n_samples = 50
n_weather_reports = 24
# the date does not matter since reports are taken from the current date on every 15 days
g = open('dataset_numerical_crops.csv', 'w')
writer_g = csv.writer(g, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
with open('dataset_crops.csv', mode='w') as f:
    writer = csv.writer(f, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
    # production data
    header = ['area']
    # n_weather_reports (last year) of that city
    for i in range(n_weather_reports):
      header.extend(['weather'+str(i), 't_max'+str(i), 't_min'+str(i), 't_avg'+str(i), 'rain_mm'+str(i), 'windSpeed'+str(i), 'windDirection'+str(i), 'humidity'+str(i), 'pressure'+str(i)])
    header.append('crop')
    # the first row is the header
    writer.writerow(header)
    writer_g.writerow(header)

    # write body of csv
    for sample in range(n_samples):
      # generate area between 10 m^2 and 500m^2
      area = round(uniform(10, 500),1)
      sample_data = [area]
      sample_data_g = [area]
      # indexes
      weather_indexes = np.random.choice(len(weather), n_weather_reports)
      directions_indexes = np.random.choice(len(wind_directions), n_weather_reports)
      for report in range(n_weather_reports):
        t_max = randint(10, 30)
        t_min = randint(0, t_max - 8)
        t_avg = randint(t_min + 3, t_max - 3)
        ww = weather[weather_indexes[report]]
        if ww == 'rainy' or ww == 'stormy' or ww == 'hurricane':
          rain_mm = randint(5, 200)
        else:
          rain_mm = 0
        sample_data.extend([ww, t_max, t_min, t_avg, rain_mm, round(uniform(0.5, 15),1), wind_directions[directions_indexes[report]], randint(10,70), randint(950, 1200)])
        sample_data_g.extend([weather_to_number(ww), t_max, t_min, t_avg, rain_mm, round(uniform(0.5, 15),1), direction_to_number(wind_directions[directions_indexes[report]]), randint(10,70), randint(950, 1200)])

      crop = np.random.choice(len(crops), 1)
      sample_data.append(crops[crop[0]])
      sample_data_g.append(crops[crop[0]])
      writer.writerow(sample_data)
      writer_g.writerow(sample_data_g)
g.close()
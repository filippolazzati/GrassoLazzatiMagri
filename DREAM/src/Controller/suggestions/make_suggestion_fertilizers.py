import numpy as np
import pandas as pd

# artificial neural network that implements the suggestion
def ANN(x, params):
  W = params[0::2]
  b = params[1::2]
  layer = x
  for i in range(len(W)):
    layer = W[i] @ layer - b[i][:,None]
    if i < len(W) - 1:
      layer = np.tanh(layer)
  layer_exp = np.exp(layer)
  return layer_exp / np.sum(layer_exp, axis = 0)

# define function for truncating float numbers
def trunc(values, decs=0):
    return np.trunc(values*10**decs)/(10**decs)

# some utility functions for moving between labels and numbers and vice versa
def convert_label_to_number(label):
  number = -1
  if label == 'Ammonium chloride':
    number = 0
  if label == 'Ammonium sulphate':
    number = 1
  if label == 'CAN':
    number = 2
  if label == 'DAP':
    number = 3
  if label == 'NP / NPK complexes':
    number = 4
  if label == 'SSP':
    number = 5
  if label == 'Urea':
    number = 6
  return number

def convert_number_to_label(number):
  label = ''
  if number == 0:
    label = 'Ammonium chloride'
  if number == 1:
    label = 'Ammonium sulphate'
  if number == 2:
    label = 'CAN'
  if number == 3:
    label = 'DAP'
  if number == 4:
    label = 'NP / NPK complexes'
  if number == 5:
    label = 'SSP'
  if number == 6:
    label = 'Urea'
  return label

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

# import the params
params = list()
for i in range(6):
    params.append(np.loadtxt('../src/Controller/suggestions/params-mean-std-fertilizers/params'+str(i)+'.csv', delimiter=','))
data_mean = np.loadtxt('../src/Controller/suggestions/params-mean-std-fertilizers/mean.csv', delimiter=',')
data_std = np.loadtxt('../src/Controller/suggestions/params-mean-std-fertilizers/std.csv', delimiter=',')

# import the sample
sample = pd.read_csv('../src/Controller/suggestions/sample.csv', header=None)

# convert sample to numeric
sample.iloc[0,0] = crop_to_number(sample.iloc[0,0])
w = 1 # index for weather
d = 7 # index for directions
for i in range(24):
    sample.iloc[0,w] = weather_to_number(sample.iloc[0,w])
    sample.iloc[0,d] = direction_to_number(sample.iloc[0,d])
    w = w + 9
    d = d + 9

# convert to numpy
x = sample.to_numpy()
# normalize the sample
x = ((x - data_mean) / data_std).transpose().astype(int)
# make suggestion
results = ANN(x, params)
suggestion = results[:,0]

# sort in descending order
suggestion = suggestion * 100
suggestion = np.sort(suggestion)[::-1]

# print the suggestions (they come back to SuggestionsController.php)
fertilizers = ['Ammonium chloride', 'Ammonium sulphate', 'CAN', 'DAP', 'NP / NPK complexes', 'SSP', 'Urea']
for i in range(len(suggestion)):
    print(fertilizers[i])
    print(',')
    print(round(suggestion[i],1))
    if i is not len(suggestion)-1:
        print(',')
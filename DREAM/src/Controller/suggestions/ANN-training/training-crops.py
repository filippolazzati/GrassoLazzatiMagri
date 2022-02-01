import numpy as np
import matplotlib.pyplot as plt
import pandas as pd
import jax.numpy as jnp
import jax
import time
from tqdm import tqdm

# import the dataset and remove the last column (that contains the labels)
dataset = pd.read_csv('dataset_numerical_crops.csv')
labels = dataset.iloc[:,-1].to_numpy()
dataset = dataset.iloc[:, :-1]
dataset = dataset.to_numpy()
dataset.shape

# normalize the dataset
data_mean = dataset.mean(axis=0)
data_std = dataset.std(axis=0)
data_normalized = (dataset - data_mean) / data_std
data_normalized.shape, data_mean.shape, data_std.shape

# define the utility functions to convert labels to numbers and vice versa
def convert_label_to_number(label):
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

def convert_number_to_label(number):
  label = ''
  if number == 0:
    label = 'potatoes'
  if number == 1:
    label = 'tomatoes'
  if number == 2:
    label = 'salad'
  if number == 3:
    label = 'onions'
  if number == 4:
    label = 'radishes'
  if number == 5:
    label = 'cucumber'
  if number == 6:
    label = 'cauliflower'
  return label

# convert labels to numbers
for i in range(len(labels)):
  labels[i] = convert_label_to_number(labels[i])
# add labels to dataset to create one-hot representation
data = np.c_[data_normalized,labels]
data.shape

labels = data[:,-1]
x_data = (data[:,1:].transpose()).astype(int)
labels.shape, x_data.shape
# create the one-hot representation to apply softmax
y_data = np.zeros((7,50))
for i in range(7):
  y_data[i, labels==i] = 1

# define the initialize params function
def initialize_params(layers_size):
  np.random.seed(0)
  params = list()
  for i in range(len(layers_size) - 1):
    params.append(np.random.randn(layers_size[i+1], layers_size[i]) * np.sqrt(2 / (layers_size[i] + layers_size[i+1])))
    params.append(np.zeros((layers_size[i+1],1)))
  return params

# define the neural network
def ANN(x, params):
  W = params[0::2]
  b = params[1::2]
  layer = x 
  for i in range(len(W)):
    layer = W[i] @ layer - b[i]
    if i < len(W) - 1:
      layer = jnp.tanh(layer)
  layer_exp = jnp.exp(layer)
  return layer_exp / jnp.sum(layer_exp, axis = 0)

# define the loss function
def MSE(x, y, params):
  return jnp.mean(jnp.square(y - ANN(x, params)))

x_train = x_data
y_train = y_data

# exploit jit to compute the gradient and compile expensive functions
grad_jit = jax.jit(jax.grad(MSE, argnums=2))
loss_jit = jax.jit(MSE)

# hyperparameters
layers_size = [217, 20, 20, 7] # last layer 7 because I want to use softmax
num_epochs = 10000
learning_rate = 1e-1

# setup
params = initialize_params(layers_size)

train_history = [loss_jit(x_train, y_train, params)]

# training loop
t0 = time.time()
for epoch in tqdm(range(num_epochs)):
  gradients = grad_jit(x_train, y_train, params)
  for i in range(len(params)):
    params[i] -= learning_rate * gradients[i]

  train_history.append(loss_jit(x_train, y_train, params))

print('elapsed time: %f' % (time.time() - t0))
print('train loss: %1.3e' % train_history[-1])
plt.loglog(train_history, label = 'train loss')
plt.legend()

# save to file the mean and the std and the parameters resulting from the training of the neural network
for i in range(len(params)):
  np.savetxt('params'+str(i)+'.csv', params[i], delimiter=',')
np.savetxt('mean.csv', data_mean, delimiter=',')
np.savetxt('std.csv', data_std, delimiter=',')
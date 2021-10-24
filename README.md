## Live API Demo

You can see Live API demo of this project at: http://ediasep-cart-api.herokuapp.com/api/documentation

## Local Installation

### Requirement

- PHP 8.0
- MySQL (version 8.0.26 recommended)
- Composer (version 2.1.5 recommended)

### Setup

After cloning the app run the following commands..

to install dependency (required):

`composer install` 

to migrate (create database and table) (required):

`php artisan migrate`

to create sample data (optional):

`php artisan db:seed`

to run test:

`php artisan test`

## Running the project

Laravel 8 comes with built in web server. You can just run the following command:

`php artisan serve`

Then open `http://127.0.0.1:8000` in your browser.

## Preventing Overselling During Checkout

Overselling is a condition when the order quantity surpass the stock of product available. It is very common in online store business. In online store it sometimes caused negative stock and it caused some fake successful order, but in reality the product stock available is not enough to be send to the customer.

Overselling can be caused mostly by concurent order. For example, the stock available for product A is 5. In the mean time, there are 10 user checkout order for the product A in the same time, each user order 1. 

It also can be caused by a user ordered with larger quantity than available stock, in this case we can simply validate the checkout and/or add to cart function.

There are several ways we can prevent this issue:

### Using Optimistic Lock

When using optimistic lock, instead of executing:

    update PRODUCT set stock = stock - quantity WHERE product_id = {id} 

we should use:

    update PRODUCT set stock = stock - quantity WHERE product_id = {id} AND stock = {old_stock}.

You can notice in the where clause, we add another filter `stock = {old_stock}`. That way, if the stock is already updated by another transaction, the affected row will be 0 and we can notify the user that their checkout failed.

### Using Optimistic Lock with Version Number

This is similar concept with previously mentioned solution, but in this case we add another field and where clause for record version:

    update PRODUCT set stock = stock - quantity, version = version + 1 WHERE product_id = {id} AND stock = {old_stock} AND version = {old_version}

We will use this strategy to prevent overselling in this case.

### Why Not Database Lock Table

Database lock is used to prevent other process to modify the table when it's being updated until the transaction completed.

It is good practice, but sometimes it will affect the database performance especially when the system has a lot of transaction to proceed.


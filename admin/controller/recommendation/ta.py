try:
  # if tradingview_ta not found, copy tradingview_ta to dist-packages or other site-packages folder
    from tradingview_ta import TA_Handler, Interval, Exchange
    import time

    # Import the library
    import argparse
    # Create the parser
    parser = argparse.ArgumentParser()
    # Add an argument
    parser.add_argument('--symbol', type=str, required=True)
    parser.add_argument('--interval', type=str, required=True)
    # Parse the argument
    args = parser.parse_args()
    # print('symbol,', args.symbol)
    # print('interval,', args.interval)

    tesla = TA_Handler(symbol=args.symbol,screener="crypto",exchange="binance",interval=args.interval,)
    #Interval.INTERVAL_15_MINUTES,)
    print(tesla.get_analysis().summary)
except Exception as inst:
  print(inst)
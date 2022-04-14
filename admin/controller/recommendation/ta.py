try:
  # if tradingview_ta not found, copy tradingview_ta to dist-packages or other site-packages folder
    from tradingview_ta import TA_Handler, Interval, Exchange
    import time
    tesla = TA_Handler(symbol="APEBUSD",screener="crypto",exchange="binance",interval=Interval.INTERVAL_15_MINUTES,)
    print(tesla.get_analysis().summary)
except Exception as inst:
  print(inst)
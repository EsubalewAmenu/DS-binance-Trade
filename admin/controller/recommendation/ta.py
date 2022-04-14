from tradingview_ta import TA_Handler, Interval, Exchange
import time
tesla = TA_Handler(symbol="APEBUSD",screener="crypto",exchange="binance",interval=Interval.INTERVAL_15_MINUTES,)
print(tesla.get_analysis().summary)

Course API Response: {course: {…}, instructor: {…}, schedules: Array(1), features: Array(4), curriculum: Array(4)}
LessonPlayer.tsx:440 Warning: React has detected a change in the order of Hooks called by LessonPlayer. This will lead to bugs and errors if not fixed. For more information, read the Rules of Hooks: https://reactjs.org/link/rules-of-hooks

   Previous render            Next render
   ------------------------------------------------------
1. useContext                 useContext
2. useContext                 useContext
3. useContext                 useContext
4. useContext                 useContext
5. useContext                 useContext
6. useContext                 useContext
7. useContext                 useContext
8. useContext                 useContext
9. useRef                     useRef
10. useContext                useContext
11. useLayoutEffect           useLayoutEffect
12. useCallback               useCallback
13. useSyncExternalStore      useSyncExternalStore
14. useDebugValue             useDebugValue
15. useContext                useContext
16. useState                  useState
17. useState                  useState
18. useContext                useContext
19. useContext                useContext
20. useContext                useContext
21. useEffect                 useEffect
22. useState                  useState
23. useCallback               useCallback
24. useSyncExternalStore      useSyncExternalStore
25. useEffect                 useEffect
26. useContext                useContext
27. useContext                useContext
28. useContext                useContext
29. useEffect                 useEffect
30. useState                  useState
31. useCallback               useCallback
32. useSyncExternalStore      useSyncExternalStore
33. useEffect                 useEffect
34. useContext                useContext
35. useContext                useContext
36. useContext                useContext
37. useEffect                 useEffect
38. useState                  useState
39. useCallback               useCallback
40. useSyncExternalStore      useSyncExternalStore
41. useEffect                 useEffect
42. useState                  useState
43. useSyncExternalStore      useSyncExternalStore
44. useDebugValue             useDebugValue
45. useEffect                 useEffect
46. useEffect                 useEffect
47. undefined                 useEffect
   ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

    at LessonPlayer (https://app.tekiplanet.org/src/components/lesson/LessonPlayer.tsx:46:34)
    at RenderedRoute (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4088:5)
    at Outlet (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4494:26)
    at div
    at div
    at PullToRefresh (https://app.tekiplanet.org/node_modules/.vite/deps/react-simple-pull-to-refresh.js?v=27763795:93:15)
    at div
    at main
    at div
    at div
    at div
    at Dashboard (https://app.tekiplanet.org/src/pages/Dashboard.tsx:49:22)
    at OnboardingGuard (https://app.tekiplanet.org/src/App.tsx:197:28)
    at ProtectedRoute (https://app.tekiplanet.org/src/App.tsx:155:27)
    at RenderedRoute (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4088:5)
    at Routes (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4558:5)
    at Suspense
    at AppContent (https://app.tekiplanet.org/src/App.tsx:252:25)
    at AppWrapper (https://app.tekiplanet.org/src/App.tsx:1129:20)
    at Router (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4501:15)
    at HashRouter (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:5283:5)
    at div
    at NotificationProvider (https://app.tekiplanet.org/src/contexts/NotificationContext.tsx:23:40)
    at Provider (https://app.tekiplanet.org/node_modules/.vite/deps/chunk-OXZDJRWN.js?v=27763795:38:15)
    at TooltipProvider (https://app.tekiplanet.org/node_modules/.vite/deps/@radix-ui_react-tooltip.js?v=27763795:65:5)
    at LoadingProvider (https://app.tekiplanet.org/src/context/LoadingContext.tsx:24:35)
    at QueryClientProvider (https://app.tekiplanet.org/node_modules/.vite/deps/@tanstack_react-query.js?v=27763795:2933:3)
    at ThemeProvider (https://app.tekiplanet.org/src/context/ThemeContext.tsx:21:33)
    at div
    at ThemeProvider (https://app.tekiplanet.org/src/theme/ThemeProvider.tsx:74:33)
    at ErrorBoundary (https://app.tekiplanet.org/src/components/errors/ErrorBoundary.tsx:8:5)
    at App (https://app.tekiplanet.org/src/App.tsx:1174:50)
printWarning @ chunk-RPCDYKBN.js?v=27763795:521
error @ chunk-RPCDYKBN.js?v=27763795:505
warnOnHookMismatchInDev @ chunk-RPCDYKBN.js?v=27763795:11495
updateHookTypesDev @ chunk-RPCDYKBN.js?v=27763795:11465
useEffect @ chunk-RPCDYKBN.js?v=27763795:12702
useEffect @ chunk-QCHXOAYK.js?v=27763795:1078
LessonPlayer @ LessonPlayer.tsx:440
renderWithHooks @ chunk-RPCDYKBN.js?v=27763795:11548
updateFunctionComponent @ chunk-RPCDYKBN.js?v=27763795:14582
beginWork @ chunk-RPCDYKBN.js?v=27763795:15924
beginWork$1 @ chunk-RPCDYKBN.js?v=27763795:19753
performUnitOfWork @ chunk-RPCDYKBN.js?v=27763795:19198
workLoopSync @ chunk-RPCDYKBN.js?v=27763795:19137
renderRootSync @ chunk-RPCDYKBN.js?v=27763795:19116
performSyncWorkOnRoot @ chunk-RPCDYKBN.js?v=27763795:18874
flushSyncCallbacks @ chunk-RPCDYKBN.js?v=27763795:9119
(anonymous) @ chunk-RPCDYKBN.js?v=27763795:18627
setTimeout
defaultScheduler @ @tanstack_react-query.js?v=27763795:565
flush @ @tanstack_react-query.js?v=27763795:589
batch @ @tanstack_react-query.js?v=27763795:607
dispatch_fn @ @tanstack_react-query.js?v=27763795:1040
setData @ @tanstack_react-query.js?v=27763795:718
onSuccess @ @tanstack_react-query.js?v=27763795:940
resolve @ @tanstack_react-query.js?v=27763795:475
Promise.then
run @ @tanstack_react-query.js?v=27763795:517
start @ @tanstack_react-query.js?v=27763795:555
fetch @ @tanstack_react-query.js?v=27763795:969
executeFetch_fn @ @tanstack_react-query.js?v=27763795:2279
onSubscribe @ @tanstack_react-query.js?v=27763795:1983
subscribe @ @tanstack_react-query.js?v=27763795:24
(anonymous) @ @tanstack_react-query.js?v=27763795:3146
subscribeToStore @ chunk-RPCDYKBN.js?v=27763795:11984
commitHookEffectListMount @ chunk-RPCDYKBN.js?v=27763795:16915
commitPassiveMountOnFiber @ chunk-RPCDYKBN.js?v=27763795:18156
commitPassiveMountEffects_complete @ chunk-RPCDYKBN.js?v=27763795:18129
commitPassiveMountEffects_begin @ chunk-RPCDYKBN.js?v=27763795:18119
commitPassiveMountEffects @ chunk-RPCDYKBN.js?v=27763795:18109
flushPassiveEffectsImpl @ chunk-RPCDYKBN.js?v=27763795:19490
flushPassiveEffects @ chunk-RPCDYKBN.js?v=27763795:19447
performSyncWorkOnRoot @ chunk-RPCDYKBN.js?v=27763795:18868
flushSyncCallbacks @ chunk-RPCDYKBN.js?v=27763795:9119
commitRootImpl @ chunk-RPCDYKBN.js?v=27763795:19432
commitRoot @ chunk-RPCDYKBN.js?v=27763795:19277
finishConcurrentRender @ chunk-RPCDYKBN.js?v=27763795:18805
performConcurrentWorkOnRoot @ chunk-RPCDYKBN.js?v=27763795:18718
workLoop @ chunk-RPCDYKBN.js?v=27763795:197
flushWork @ chunk-RPCDYKBN.js?v=27763795:176
performWorkUntilDeadline @ chunk-RPCDYKBN.js?v=27763795:384Understand this error
chunk-RPCDYKBN.js?v=27763795:11678 Uncaught Error: Rendered more hooks than during the previous render.
    at updateWorkInProgressHook (chunk-RPCDYKBN.js?v=27763795:11678:21)
    at updateEffectImpl (chunk-RPCDYKBN.js?v=27763795:12074:22)
    at updateEffect (chunk-RPCDYKBN.js?v=27763795:12099:18)
    at Object.useEffect (chunk-RPCDYKBN.js?v=27763795:12703:22)
    at useEffect (chunk-QCHXOAYK.js?v=27763795:1078:29)
    at LessonPlayer (LessonPlayer.tsx:440:3)
    at renderWithHooks (chunk-RPCDYKBN.js?v=27763795:11548:26)
    at updateFunctionComponent (chunk-RPCDYKBN.js?v=27763795:14582:28)
    at beginWork (chunk-RPCDYKBN.js?v=27763795:15924:22)
    at HTMLUnknownElement.callCallback2 (chunk-RPCDYKBN.js?v=27763795:3674:22)
updateWorkInProgressHook @ chunk-RPCDYKBN.js?v=27763795:11678
updateEffectImpl @ chunk-RPCDYKBN.js?v=27763795:12074
updateEffect @ chunk-RPCDYKBN.js?v=27763795:12099
useEffect @ chunk-RPCDYKBN.js?v=27763795:12703
useEffect @ chunk-QCHXOAYK.js?v=27763795:1078
LessonPlayer @ LessonPlayer.tsx:440
renderWithHooks @ chunk-RPCDYKBN.js?v=27763795:11548
updateFunctionComponent @ chunk-RPCDYKBN.js?v=27763795:14582
beginWork @ chunk-RPCDYKBN.js?v=27763795:15924
callCallback2 @ chunk-RPCDYKBN.js?v=27763795:3674
invokeGuardedCallbackDev @ chunk-RPCDYKBN.js?v=27763795:3699
invokeGuardedCallback @ chunk-RPCDYKBN.js?v=27763795:3733
beginWork$1 @ chunk-RPCDYKBN.js?v=27763795:19765
performUnitOfWork @ chunk-RPCDYKBN.js?v=27763795:19198
workLoopSync @ chunk-RPCDYKBN.js?v=27763795:19137
renderRootSync @ chunk-RPCDYKBN.js?v=27763795:19116
performSyncWorkOnRoot @ chunk-RPCDYKBN.js?v=27763795:18874
flushSyncCallbacks @ chunk-RPCDYKBN.js?v=27763795:9119
(anonymous) @ chunk-RPCDYKBN.js?v=27763795:18627
setTimeout
defaultScheduler @ @tanstack_react-query.js?v=27763795:565
flush @ @tanstack_react-query.js?v=27763795:589
batch @ @tanstack_react-query.js?v=27763795:607
dispatch_fn @ @tanstack_react-query.js?v=27763795:1040
setData @ @tanstack_react-query.js?v=27763795:718
onSuccess @ @tanstack_react-query.js?v=27763795:940
resolve @ @tanstack_react-query.js?v=27763795:475
Promise.then
run @ @tanstack_react-query.js?v=27763795:517
start @ @tanstack_react-query.js?v=27763795:555
fetch @ @tanstack_react-query.js?v=27763795:969
executeFetch_fn @ @tanstack_react-query.js?v=27763795:2279
onSubscribe @ @tanstack_react-query.js?v=27763795:1983
subscribe @ @tanstack_react-query.js?v=27763795:24
(anonymous) @ @tanstack_react-query.js?v=27763795:3146
subscribeToStore @ chunk-RPCDYKBN.js?v=27763795:11984
commitHookEffectListMount @ chunk-RPCDYKBN.js?v=27763795:16915
commitPassiveMountOnFiber @ chunk-RPCDYKBN.js?v=27763795:18156
commitPassiveMountEffects_complete @ chunk-RPCDYKBN.js?v=27763795:18129
commitPassiveMountEffects_begin @ chunk-RPCDYKBN.js?v=27763795:18119
commitPassiveMountEffects @ chunk-RPCDYKBN.js?v=27763795:18109
flushPassiveEffectsImpl @ chunk-RPCDYKBN.js?v=27763795:19490
flushPassiveEffects @ chunk-RPCDYKBN.js?v=27763795:19447
performSyncWorkOnRoot @ chunk-RPCDYKBN.js?v=27763795:18868
flushSyncCallbacks @ chunk-RPCDYKBN.js?v=27763795:9119
commitRootImpl @ chunk-RPCDYKBN.js?v=27763795:19432
commitRoot @ chunk-RPCDYKBN.js?v=27763795:19277
finishConcurrentRender @ chunk-RPCDYKBN.js?v=27763795:18805
performConcurrentWorkOnRoot @ chunk-RPCDYKBN.js?v=27763795:18718
workLoop @ chunk-RPCDYKBN.js?v=27763795:197
flushWork @ chunk-RPCDYKBN.js?v=27763795:176
performWorkUntilDeadline @ chunk-RPCDYKBN.js?v=27763795:384Understand this error
chunk-RPCDYKBN.js?v=27763795:11678 Uncaught Error: Rendered more hooks than during the previous render.
    at updateWorkInProgressHook (chunk-RPCDYKBN.js?v=27763795:11678:21)
    at updateEffectImpl (chunk-RPCDYKBN.js?v=27763795:12074:22)
    at updateEffect (chunk-RPCDYKBN.js?v=27763795:12099:18)
    at Object.useEffect (chunk-RPCDYKBN.js?v=27763795:12703:22)
    at useEffect (chunk-QCHXOAYK.js?v=27763795:1078:29)
    at LessonPlayer (LessonPlayer.tsx:440:3)
    at renderWithHooks (chunk-RPCDYKBN.js?v=27763795:11548:26)
    at updateFunctionComponent (chunk-RPCDYKBN.js?v=27763795:14582:28)
    at beginWork (chunk-RPCDYKBN.js?v=27763795:15924:22)
    at HTMLUnknownElement.callCallback2 (chunk-RPCDYKBN.js?v=27763795:3674:22)
updateWorkInProgressHook @ chunk-RPCDYKBN.js?v=27763795:11678
updateEffectImpl @ chunk-RPCDYKBN.js?v=27763795:12074
updateEffect @ chunk-RPCDYKBN.js?v=27763795:12099
useEffect @ chunk-RPCDYKBN.js?v=27763795:12703
useEffect @ chunk-QCHXOAYK.js?v=27763795:1078
LessonPlayer @ LessonPlayer.tsx:440
renderWithHooks @ chunk-RPCDYKBN.js?v=27763795:11548
updateFunctionComponent @ chunk-RPCDYKBN.js?v=27763795:14582
beginWork @ chunk-RPCDYKBN.js?v=27763795:15924
callCallback2 @ chunk-RPCDYKBN.js?v=27763795:3674
invokeGuardedCallbackDev @ chunk-RPCDYKBN.js?v=27763795:3699
invokeGuardedCallback @ chunk-RPCDYKBN.js?v=27763795:3733
beginWork$1 @ chunk-RPCDYKBN.js?v=27763795:19765
performUnitOfWork @ chunk-RPCDYKBN.js?v=27763795:19198
workLoopSync @ chunk-RPCDYKBN.js?v=27763795:19137
renderRootSync @ chunk-RPCDYKBN.js?v=27763795:19116
recoverFromConcurrentError @ chunk-RPCDYKBN.js?v=27763795:18736
performSyncWorkOnRoot @ chunk-RPCDYKBN.js?v=27763795:18879
flushSyncCallbacks @ chunk-RPCDYKBN.js?v=27763795:9119
(anonymous) @ chunk-RPCDYKBN.js?v=27763795:18627
setTimeout
defaultScheduler @ @tanstack_react-query.js?v=27763795:565
flush @ @tanstack_react-query.js?v=27763795:589
batch @ @tanstack_react-query.js?v=27763795:607
dispatch_fn @ @tanstack_react-query.js?v=27763795:1040
setData @ @tanstack_react-query.js?v=27763795:718
onSuccess @ @tanstack_react-query.js?v=27763795:940
resolve @ @tanstack_react-query.js?v=27763795:475
Promise.then
run @ @tanstack_react-query.js?v=27763795:517
start @ @tanstack_react-query.js?v=27763795:555
fetch @ @tanstack_react-query.js?v=27763795:969
executeFetch_fn @ @tanstack_react-query.js?v=27763795:2279
onSubscribe @ @tanstack_react-query.js?v=27763795:1983
subscribe @ @tanstack_react-query.js?v=27763795:24
(anonymous) @ @tanstack_react-query.js?v=27763795:3146
subscribeToStore @ chunk-RPCDYKBN.js?v=27763795:11984
commitHookEffectListMount @ chunk-RPCDYKBN.js?v=27763795:16915
commitPassiveMountOnFiber @ chunk-RPCDYKBN.js?v=27763795:18156
commitPassiveMountEffects_complete @ chunk-RPCDYKBN.js?v=27763795:18129
commitPassiveMountEffects_begin @ chunk-RPCDYKBN.js?v=27763795:18119
commitPassiveMountEffects @ chunk-RPCDYKBN.js?v=27763795:18109
flushPassiveEffectsImpl @ chunk-RPCDYKBN.js?v=27763795:19490
flushPassiveEffects @ chunk-RPCDYKBN.js?v=27763795:19447
performSyncWorkOnRoot @ chunk-RPCDYKBN.js?v=27763795:18868
flushSyncCallbacks @ chunk-RPCDYKBN.js?v=27763795:9119
commitRootImpl @ chunk-RPCDYKBN.js?v=27763795:19432
commitRoot @ chunk-RPCDYKBN.js?v=27763795:19277
finishConcurrentRender @ chunk-RPCDYKBN.js?v=27763795:18805
performConcurrentWorkOnRoot @ chunk-RPCDYKBN.js?v=27763795:18718
workLoop @ chunk-RPCDYKBN.js?v=27763795:197
flushWork @ chunk-RPCDYKBN.js?v=27763795:176
performWorkUntilDeadline @ chunk-RPCDYKBN.js?v=27763795:384Understand this error
chunk-RPCDYKBN.js?v=27763795:14032 The above error occurred in the <LessonPlayer> component:

    at LessonPlayer (https://app.tekiplanet.org/src/components/lesson/LessonPlayer.tsx:46:34)
    at RenderedRoute (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4088:5)
    at Outlet (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4494:26)
    at div
    at div
    at PullToRefresh (https://app.tekiplanet.org/node_modules/.vite/deps/react-simple-pull-to-refresh.js?v=27763795:93:15)
    at div
    at main
    at div
    at div
    at div
    at Dashboard (https://app.tekiplanet.org/src/pages/Dashboard.tsx:49:22)
    at OnboardingGuard (https://app.tekiplanet.org/src/App.tsx:197:28)
    at ProtectedRoute (https://app.tekiplanet.org/src/App.tsx:155:27)
    at RenderedRoute (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4088:5)
    at Routes (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4558:5)
    at Suspense
    at AppContent (https://app.tekiplanet.org/src/App.tsx:252:25)
    at AppWrapper (https://app.tekiplanet.org/src/App.tsx:1129:20)
    at Router (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:4501:15)
    at HashRouter (https://app.tekiplanet.org/node_modules/.vite/deps/react-router-dom.js?v=27763795:5283:5)
    at div
    at NotificationProvider (https://app.tekiplanet.org/src/contexts/NotificationContext.tsx:23:40)
    at Provider (https://app.tekiplanet.org/node_modules/.vite/deps/chunk-OXZDJRWN.js?v=27763795:38:15)
    at TooltipProvider (https://app.tekiplanet.org/node_modules/.vite/deps/@radix-ui_react-tooltip.js?v=27763795:65:5)
    at LoadingProvider (https://app.tekiplanet.org/src/context/LoadingContext.tsx:24:35)
    at QueryClientProvider (https://app.tekiplanet.org/node_modules/.vite/deps/@tanstack_react-query.js?v=27763795:2933:3)
    at ThemeProvider (https://app.tekiplanet.org/src/context/ThemeContext.tsx:21:33)
    at div
    at ThemeProvider (https://app.tekiplanet.org/src/theme/ThemeProvider.tsx:74:33)
    at ErrorBoundary (https://app.tekiplanet.org/src/components/errors/ErrorBoundary.tsx:8:5)
    at App (https://app.tekiplanet.org/src/App.tsx:1174:50)

React will try to recreate this component tree from scratch using the error boundary you provided, ErrorBoundary.
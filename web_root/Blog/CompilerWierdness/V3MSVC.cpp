double RunBenchmarkInternal<struct_Version3>(Version3 *param_1,duration<__int64,struct_std::ratio<1,1000000000>_> *param_2)
{
  int iVar1;
  longlong lVar2;
  longlong lVar3;
  longlong lVar4;
  longlong lVar5;
  MyData *pMVar6;
  longlong lVar7;
  float *pfVar8;
  MyData *pMVar9;
  double dVar10;
  float fVar11;
  Version3 *version;
  duration<__int64,std::ratio<1,1000000000>> *totalRuntime;
  
  fVar11 = 1.00000000;
  dVar10 = 0.00000000;
  lVar7 = 0x32;
  do {
    lVar2 = _Query_perf_frequency();
    lVar3 = _Query_perf_counter();
    iVar1 = rand();
    pMVar6 = (param_1->Data)._Mypair._Myval2._Mylast;
    pMVar9 = (param_1->Data)._Mypair._Myval2._Myfirst;
    if (pMVar9 != pMVar6) {
      pfVar8 = pMVar9->ActualData1 + 1;
      do {
        if (iVar1 == ((iVar1 / 100 + (iVar1 >> 0x1f)) - (int)((longlong)iVar1 * 0x51eb851f >> 0x3f))
                     * 100) {
          pfVar8[0x20] = (((((0.00000000 - (pfVar8[7] + pfVar8[-1])) + pfVar8[8] + *pfVar8) *
                           (pfVar8[9] + pfVar8[1] + fVar11)) / (fVar11 - (pfVar8[10] + pfVar8[2])) -
                          (pfVar8[0xb] + pfVar8[3])) / (pfVar8[0xc] + pfVar8[4] + fVar11)) *
                         (pfVar8[0xd] + pfVar8[5] + fVar11) + pfVar8[0xe] + pfVar8[6];
        }
        iVar1 = iVar1 + 1;
        pMVar9 = pMVar9 + 1;
        pfVar8 = pfVar8 + 0xb8;
      } while (pMVar9 != pMVar6);
    }
                    /* Symbol Ref: now */
    lVar4 = _Query_perf_frequency();
    lVar5 = _Query_perf_counter();
    pMVar6 = (param_1->Data)._Mypair._Myval2._Myfirst;
    while (pMVar6 != (param_1->Data)._Mypair._Myval2._Mylast) {
      pfVar8 = &pMVar6->Result;
      pMVar6 = pMVar6 + 1;
      dVar10 = dVar10 + (double)*pfVar8;
    }
    *(longlong *)param_2 =
         *(longlong *)param_2 +
         ((((lVar5 % lVar4) * 1000000000) / lVar4 + (lVar5 / lVar4) * 1000000000) -
         (((lVar3 % lVar2) * 1000000000) / lVar2 + (lVar3 / lVar2) * 1000000000));
    lVar7 = lVar7 + -1;
  } while (lVar7 != 0);
  return dVar10;
}
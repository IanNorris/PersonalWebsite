double RunBenchmarkInternal<Version3>(Version3 *param_1,duration *param_2)
{
  int iVar1;
  long lVar2;
  long lVar3;
  long lVar4;
  int iVar5;
  double dVar6;
  
  iVar5 = 0;
  dVar6 = 0.00000000;
  do {
    lVar2 = now();
    iVar1 = rand();
    lVar4 = *(long *)param_1;
    lVar3 = *(long *)(param_1 + 8);
    while (lVar4 != lVar3) {
      if (iVar1 == ((iVar1 / 100 + (iVar1 >> 0x1f)) - (int)((long)iVar1 * 0x51eb851f >> 0x3f)) * 100
         ) {
        *(float *)(lVar4 + 0x2d8) =
             *(float *)(lVar4 + 0x270) + *(float *)(lVar4 + 0x290) +
             (*(float *)(lVar4 + 0x26c) + *(float *)(lVar4 + 0x28c) + 1.00000000) *
             ((((*(float *)(lVar4 + 0x25c) + *(float *)(lVar4 + 0x27c) + 1.00000000) *
               (*(float *)(lVar4 + 600) + *(float *)(lVar4 + 0x278) +
               (0.00000000 - (*(float *)(lVar4 + 0x254) + *(float *)(lVar4 + 0x274))))) /
               (1.00000000 - (*(float *)(lVar4 + 0x260) + *(float *)(lVar4 + 0x280))) -
              (*(float *)(lVar4 + 0x264) + *(float *)(lVar4 + 0x284))) /
             (*(float *)(lVar4 + 0x268) + *(float *)(lVar4 + 0x288) + 1.00000000));
      }
      lVar4 = lVar4 + 0x2e0;
      iVar1 = iVar1 + 1;
    }
    lVar3 = now();
    lVar4 = *(long *)param_1;
    while (lVar4 != *(long *)(param_1 + 8)) {
      dVar6 = dVar6 + (double)*(float *)(lVar4 + 0x2d8);
      lVar4 = lVar4 + 0x2e0;
    }
    *(long *)param_2 = *(long *)param_2 + (lVar3 - lVar2);
    iVar5 = iVar5 + 1;
  } while (iVar5 != 0x32);
  return dVar6;
}
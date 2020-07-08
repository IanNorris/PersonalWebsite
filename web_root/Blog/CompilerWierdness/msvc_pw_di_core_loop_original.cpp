Version *version = ...;
int writeOffset = ...;

float *pfVar2;
//The 0th offset from 
Version* puVar1 = *(Version **)version;
while (version != puVar1)
{
	if (writeOffset == ((writeOffset / 100 + (writeOffset >> 0x1f)) - (int)((long)writeOffset * 0x51eb851f >> 0x3f)) * 100) 
	{
		pfVar2 = (float *)puVar1[2];
		*(float *)(puVar1 + 0x5b) =
			pfVar2[7] + *(float *)(puVar1 + 0x52) +
			(pfVar2[6] + *(float *)((long)puVar1 + 0x28c) + 1.00000000) *
			((((pfVar2[2] + *(float *)((long)puVar1 + 0x27c) + 1.00000000) *
			(pfVar2[1] + *(float *)(puVar1 + 0x4f) +
			(0.00000000 - (*pfVar2 + *(float *)((long)puVar1 + 0x274))))) /
			(1.00000000 - (pfVar2[3] + *(float *)(puVar1 + 0x50))) -
			(pfVar2[4] + *(float *)((long)puVar1 + 0x284))) /
			(pfVar2[5] + *(float *)(puVar1 + 0x51) + 1.00000000));
	}
	puVar1 = (Version *)*puVar1;
	writeOffset = writeOffset + 1;
}
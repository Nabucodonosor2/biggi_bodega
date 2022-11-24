alter  PROCEDURE spx_busca_empresa_inexistente
AS
BEGIN  
	declare @nro_nv		numeric
			,@count			numeric
			,@rut			numeric

	declare @TEMPO TABLE 	 
				(nro_nv numeric
				,rut numeric)

	declare c_aux_nv cursor for 
	select distinct convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1)) rut
	from aux_nota_venta

	open c_aux_nv 
	fetch c_aux_nv into @rut
	while @@fetch_status = 0 begin
		select @count=count(*) from empresa where rut = @rut
		if (@count=0) begin
			select top 1 @nro_nv=numero_nota_venta from aux_nota_venta where convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1))=@rut
			insert into @TEMPO values (@nro_nv, @rut)
		end

		fetch c_aux_nv into @rut
	end
	close c_aux_nv
	deallocate c_aux_nv

	select * from @TEMPO
END






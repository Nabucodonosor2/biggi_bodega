CREATE PROCEDURE [dbo].[spx_busca_empresa_inexistente_gd]
AS
BEGIN  
	declare @nro_gd		numeric
			,@count			numeric
			,@rut			numeric

	declare @TEMPO TABLE 	 
				(nro_gd numeric
				,rut numeric)

	declare c_aux_gd cursor for 
	select distinct convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1)) rut
	from aux_guia_despacho

	open c_aux_gd 
	fetch c_aux_gd into @rut
	while @@fetch_status = 0 begin
		select @count=count(*) from empresa where rut = @rut
		if (@count=0) begin
			select top 1 @nro_gd=numero_guia_despacho from aux_guia_despacho where convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1))=@rut
			insert into @TEMPO values (@nro_gd, @rut)
		end

		fetch c_aux_gd into @rut
	end
	close c_aux_gd
	deallocate c_aux_gd

	select * from @TEMPO
END
go